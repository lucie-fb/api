<?php

namespace App\Service;

use App\Dto\Cart\CartAddLineInput;
use App\Dto\Cart\CartDetailsOutput;
use App\Dto\Cart\CartLineOutput;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Enum\CartStatus;
use App\Entity\User;
use App\Exception\Cart\CartAlreadyPaidException;
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
        private readonly TripService    $tripService,
        private readonly CartRepository $cartRepository,
        private readonly AuditService   $audit,
    )
    {
    }

    /**
     * Returns the pending cart owned by this user, if any.
     */
    public function findActiveFor(User $user): ?Cart
    {
        return $this->cartRepository->findActiveFor($user);
    }

    public function open(User $user): Cart
    {
        $existing = $this->findActiveFor($user);

        if(null === $existing) {
            return $existing;
        }

        $cart = new Cart();

        $this->audit->stampCreation($cart);
        $this->cartRepository->persist($cart);
        $this->cartRepository->flush();
        return $cart;
    }

    /**
     * Adds a line to this cart and returns the cart itself.
     *
     * @throws CartAlreadyPaidException when the cart is no longer modifiable
     * @throws TripNotFoundException    when no trip carries the submitted identifier
     * @throws CartAlreadyPaidException when the cart is no longer modifiable
     */
    public function addLine(Cart $cart, CartAddLineInput $input): Cart
    {
        // résoudre un lancer appartient au domaine des lancers : ce service passe par le sien
        $trip = $this->tripService->findOneById(Uuid::fromString($input->tripId));

        $item = new CartItem()
            ->setTrip($trip)
            ->setPassengers($input->passengers);

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
     * Returns the cart carrying this identifier.
     *
     * @throws CartNotFoundException when no cart carries this identifier
     */
    public function findOneById(\Symfony\Component\Validator\Constraints\Uuid $id): Cart
    {
        $cart = $this->cartRepository->find($id);
        if (null === $cart) {
            throw new CartNotFoundException();
        }

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
    if(CartStatus::Paid === $cart->getStatus()) {
        throw new CartAlreadyPaidException();
    }
    $line = null;
    $line = $cart
        ->getItems()
        ->findFirst(static fn($_, $item): bool => $item->getId()->equals($lineId),
        );

    if (null === $line) {
        throw new CartLineNotFoundException();
    }

    $this->audit->markDeleted($line);

    $alive = $cart
        ->getItems()
        ->filter(static fn (CartItem $item): bool => null === $item->getDeletedAt(),
        );

    if($alive->isEmpty()) {
        $this->audit->markDeleted($cart);
    }

    $this->cartRepository->flush();

    }
}
