<?php

namespace App\State\Trip;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\TripService;

final readonly class TripItemProvider implements ProviderInterface
{
    public function __construct(
        private TripService $tripService,
    ) {
    }

    /**
     * Resolves the trip identifier carried by the URL to its detailed representation.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // aucune conversion : Trip::$id étant typé Uuid, le transformer d'API Platform livre
        // déjà un Symfony\Component\Uid\UuidV7 dans $uriVariables['id'], pas une chaîne
        return $this->tripService->toDetails($this->tripService->findOneById($uriVariables['id']));
    }
}
