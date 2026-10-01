<?php

namespace App\State\City;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\City\CityListOutput;
use App\Service\CityService;

class CityCollectionProvider implements ProviderInterface
{
    // le service n'est pas construit ici, il est demandé au conteneur
    public function __construct(
        private readonly CityService $cityService,
    ) {
    }

    /**
     * Serves the city collection, already mapped onto its output payload.
     *
     * @return CityListOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        // API Platform dépose les paramètres de requête dans le contexte, sous la clé « filters »
        $filters = $context['filters'] ?? [];

        return array_map(
            $this->cityService->toList(...),
            $this->cityService->search(
                $filters['q'] ?? null,
                isset($filters['limit']) ? (int) $filters['limit'] : null,
            ),
        );
    }
}
