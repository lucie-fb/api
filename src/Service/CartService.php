<?php

namespace App\Service;

use App\Dto\Cart\CartAddLineInput;
use App\Dto\Cart\CartDetailsOutput;
use App\Dto\Cart\CartLineOutput;
use App\Dto\Cart\CartPayOutput;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Enum\CartStatus;
use App\Entity\Ticket;
use App\Entity\User;
use App\Exception\Cart\CartAlreadyPaidException;
use App\Exception\Cart\CartEmptyException;
use App\Exception\Cart\CartLineNotFoundException;
use App\Exception\Cart\CartNotFoundException;
use App\Exception\Trip\TripNotFoundException;
use App\Repository\CartRepository;
use App\Service\Utils\AuditService;
use Symfony\Component\Uid\Uuid;

class CartService
{
    // aucun collaborateur n'est construit ici, tous sont demandés au conteneur
    public function __construct(
        private readonly TripService $tripService,
        private readonly TicketService $ticketService,
        private readonly CartRepository $cartRepository,
        private readonly AuditService $audit,
    ) {
    }

    /**
     * Returns the pending cart owned by this user, if any.
     */
    public function findActiveFor(User $user): ?Cart
    {
        return $this->cartRepository->findActiveFor($user);
    }

    /**
     * Returns the pending cart owned by this user, opening one when there is none.
     */
    public function open(User $user): Cart
    {
        $existing = $this->findActiveFor($user);

        // un panier en cours existe déjà : l'invariant du contrat interdit d'en ouvrir un second
        if (null !== $existing) {
            return $existing;
        }

        $cart = new Cart();

        // l'appel se fait dans une requête authentifiée : created_by_id est cette fois rempli
        $this->audit->stampCreation($cart);

        $this->cartRepository->persist($cart);
        $this->cartRepository->flush();

        return $cart;
    }

    /**
     * Returns the cart carrying this identifier.
     *
     * @throws CartNotFoundException when no cart carries this identifier
     */
    public function findOneById(Uuid $id): Cart
    {
        // find() est héritée de Doctrine : rien à écrire dans le repository pour un accès par clé
        $cart = $this->cartRepository->find($id);

        if (null === $cart) {
            throw new CartNotFoundException();
        }

        return $cart;
    }

    /**
     * Adds a line to this cart and returns the cart itself.
     *
     * @throws CartAlreadyPaidException when the cart is no longer modifiable
     * @throws TripNotFoundException    when no trip carries the submitted identifier
     */
    public function addLine(Cart $cart, CartAddLineInput $input): Cart
    {
        // l'état se compare au cas d'enum, jamais à la chaîne
        if (CartStatus::Paid === $cart->getStatus()) {
            throw new CartAlreadyPaidException();
        }

        // résoudre un lancer appartient au domaine des lancers : ce service passe par le sien
        $trip = $this->tripService->findOneById(Uuid::fromString($input->tripId));

        $item = new CartItem();
        $item->setTrip($trip);
        $item->setPassengers($input->passengers);

        // aucune garde de doublon : le même lancer peut figurer deux fois, une ligne vaut un lancer
        // et un nombre de places
        $cart->addItem($item);

        $this->audit->stampCreation($item);

        // aucun persist explicite : Cart::$items porte cascade: ['persist'] depuis le rendez-vous 1.
        // La ligne s'écrit donc par le repository de l'agrégat, pas par celui des lignes
        $this->cartRepository->flush();

        return $cart;
    }

    /**
     * Soft-deletes a line of this cart, and the cart itself when it was the last one.
     *
     * @throws CartAlreadyPaidException  when the cart is no longer modifiable
     * @throws CartLineNotFoundException when this cart carries no such line
     */
    public function removeLine(Cart $cart, Uuid $lineId): void
    {
        // la garde vient en premier : un panier payé ne bouge plus, même pour retirer
        if (CartStatus::Paid === $cart->getStatus()) {
            throw new CartAlreadyPaidException();
        }

        $line = null;

        // la ligne se cherche dans la collection du panier, pas par le repository : une ligne
        // appartenant au panier de quelqu'un d'autre ne doit tout simplement pas se trouver
        foreach ($cart->getItems() as $item) {
            if ($item->getId()->equals($lineId)) {
                $line = $item;

                break;
            }
        }

        if (null === $line) {
            throw new CartLineNotFoundException();
        }

        // retirer une ligne l'estampille : elle reste dans la collection et reste en base.
        // L'estampille passe par le service d'audit, comme la création : lui seul sait qui
        // porte la requête, et deleted_by_id resterait vide sans lui
        $this->audit->markDeleted($line);

        // la ligne qu'on vient d'estampiller est encore dans la collection : le décompte des
        // vivantes se fait donc sur les dates de suppression, pas sur la taille de la collection
        $alive = $cart->getItems()->filter(
            static fn (CartItem $item): bool => null === $item->getDeletedAt(),
        );

        // c'était la dernière : le panier part avec elle, dans le même mouvement
        if ($alive->isEmpty()) {
            $this->audit->markDeleted($cart);
        }

        $this->cartRepository->flush();
    }

    /**
     * Pays this cart: issues its tickets and marks it as paid, in a single write.
     *
     * @return Ticket[] the tickets issued
     *
     * @throws CartAlreadyPaidException when the cart has already been paid
     * @throws CartEmptyException       when the cart carries no line
     */
    public function pay(Cart $cart): array
    {
        if (CartStatus::Paid === $cart->getStatus()) {
            throw new CartAlreadyPaidException();
        }

        // une ligne retirée n'est pas chargée, le filtre de suppression douce l'écarte : elle ne
        // compte pas, et ne donnera aucun billet
        if ($cart->getItems()->isEmpty()) {
            throw new CartEmptyException();
        }

        // fabriquer un billet appartient au domaine des billets : ce service passe par le sien
        $tickets = $this->ticketService->issue($cart);

        $cart->setStatus(CartStatus::Paid);

        // la première modification du module qui n'est ni une création ni une suppression
        $this->audit->stampUpdate($cart);

        // un seul flush, donc une seule transaction : les billets et le statut s'écrivent
        // ensemble, ou pas du tout. Le flush d'un repository écrit tout ce que l'unité de travail
        // a en attente, les billets persistés par le leur compris
        $this->cartRepository->flush();

        return $tickets;
    }

    /**
     * Returns the confirmation reference of this cart, derived from its identity.
     */
    public function confirmationOf(Cart $cart): string
    {
        // la fin de l'identifiant, pas son début : un UUID v7 commence par son horodatage
        $suffix = strtoupper(substr($cart->getId()->toRfc4122(), -6));

        return sprintf('ONTB-%s-%s', $cart->getCreatedAt()->format('Y'), $suffix);
    }

    /**
     * Maps a paid cart and its tickets onto the payload served by the payment endpoint.
     *
     * @param Ticket[] $tickets
     */
    public function toPayment(Cart $cart, array $tickets): CartPayOutput
    {
        return new CartPayOutput(
            confirmation: $this->confirmationOf($cart),
            // la transformation d'un billet reste au domaine des billets
            tickets: array_map($this->ticketService->toList(...), $tickets),
        );
    }

    /**
     * Maps a cart item onto the payload served inside a cart.
     */
    public function toLine(CartItem $item): CartLineOutput
    {
        $trip = $item->getTrip();

        return new CartLineOutput(
            id: $item->getId(),
            // la transformation d'un lancer reste au domaine des lancers
            trip: $this->tripService->toList($trip),
            passengers: $item->getPassengers(),
            // le sous-total ne vient d'aucune colonne : il se recalcule à chaque lecture
            subtotal: $trip->getPrice() * $item->getPassengers(),
        );
    }

    /**
     * Maps a cart onto the payload served by the cart endpoints.
     */
    public function toDetails(Cart $cart): CartDetailsOutput
    {
        // la boucle appartient au service, parce que la ligne appartient au panier
        $lines = array_map($this->toLine(...), $cart->getItems()->toArray());

        return new CartDetailsOutput(
            id: $cart->getId(),
            status: $cart->getStatus(),
            items: $lines,
            total: array_sum(array_column($lines, 'subtotal')),
            createdAt: $cart->getCreatedAt(),
        );
    }
}
