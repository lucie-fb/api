<?php

namespace App\State\Ticket;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Ticket\TicketListOutput;
use App\Entity\User;
use App\Service\TicketService;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<TicketListOutput>
 */
final class TicketCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly TicketService $ticketService,
    ) {
    }

    /**
     * Serves the tickets of the authenticated traveller, most recent first.
     *
     * @return TicketListOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();

        // on ne protège pas une collection, on la filtre : sans porteur identifiable, elle est vide
        if (!$user instanceof User) {
            return [];
        }

        // un state ne fabrique aucun DTO : il appelle la méthode du service qui le fabrique
        return array_map($this->ticketService->toList(...), $this->ticketService->findFor($user));
    }
}
