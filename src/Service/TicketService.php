<?php

namespace App\Service;

use App\Dto\Ticket\TicketListOutput;
use App\Entity\Cart;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Service\Utils\AuditService;

class TicketService
{
    // aucun collaborateur n'est construit ici, tous sont demandés au conteneur
    public function __construct(
        private readonly TripService $tripService,
        private readonly TicketRepository $ticketRepository,
        private readonly AuditService $audit,
    ) {
    }

    /**
     * Builds one ticket per reserved seat of this cart, persisted but not flushed.
     *
     * @return Ticket[]
     */
    public function issue(Cart $cart): array
    {
        $issued = [];

        foreach ($cart->getItems() as $line) {
            $trip = $line->getTrip();

            // une ligne de N places donne N billets : un billet vaut une place, pas une personne
            for ($seat = 0; $seat < $line->getPassengers(); ++$seat) {
                $ticket = new Ticket();
                $ticket->setTrip($trip);
                $ticket->setCart($cart);
                // le prix se copie maintenant : c'est celui qui est acquitté
                $ticket->setPrice($trip->getPrice());

                $this->audit->stampCreation($ticket);
                $this->ticketRepository->persist($ticket);

                $issued[] = $ticket;
            }
        }

        // aucun flush : c'est le panier qui décide quand tout s'écrit, en une seule fois
        return $issued;
    }

    /**
     * Returns the tickets owned by this user, most recent first.
     *
     * @return Ticket[]
     */
    public function findFor(User $user): array
    {
        return $this->ticketRepository->findFor($user);
    }

    /**
     * Maps a ticket onto the payload served by the ticket endpoints.
     */
    public function toList(Ticket $ticket): TicketListOutput
    {
        return new TicketListOutput(
            id: $ticket->getId(),
            // la transformation d'un lancer reste au domaine des lancers
            trip: $this->tripService->toList($ticket->getTrip()),
            price: $ticket->getPrice(),
            createdAt: $ticket->getCreatedAt(),
        );
    }
}
