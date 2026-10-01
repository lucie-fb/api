<?php

namespace App\Service;

use App\Dto\City\CityListOutput;
use App\Entity\City;
use App\Repository\CityRepository;

class CityService
{
    public const DEFAULT_LIMIT = 20;
    public const MAX_LIMIT = 100;

    // le repository n'est pas construit ici, il est demandé au conteneur
    public function __construct(
        private readonly CityRepository $cities,
    ) {
    }

    /**
     * Searches cities by name, with a safe upper bound on the result count.
     *
     * A blank filter is treated as no filter at all.
     *
     * @return City[]
     */
    public function search(?string $q, ?int $limit): array
    {
        $pattern = null === $q ? null : trim($q);
        if ('' === $pattern) {
            $pattern = null;
        }

        // programmation défensive : un service de domaine ne présume pas que son appelant a validé
        $boundedLimit = min(self::MAX_LIMIT, max(1, $limit ?? self::DEFAULT_LIMIT));

        return $this->cities->search($pattern, $boundedLimit);
    }

    /**
     * Maps a city onto the payload served by the collection endpoint.
     */
    public function toList(City $city): CityListOutput
    {
        return new CityListOutput($city->getId(), $city->getName());
    }
}
