<?php

namespace App\State\Trip;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Trip\TripListOutput;
use App\Dto\Trip\TripSearchInput;
use App\Service\TripService;

/**
 * @implements ProcessorInterface<TripSearchInput, TripListOutput[]>
 */
final readonly class TripSearchProcessor implements ProcessorInterface
{
    // le service n'est pas construit ici, il est demandé au conteneur
    public function __construct(
        private TripService $tripService,
    ) {
    }

    /**
     * Serves the trips matching the submitted search, mapped onto their list payload.
     *
     * @return TripListOutput[]
     * @throws \DateMalformedStringException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map($this->tripService->toList(...), $this->tripService->search($data));
    }
}
