<?php

namespace App\Service;

use App\Dto\City\CityListOutput;
use App\Entity\City;
use App\Exception\City\CityNotFoundException;
use App\Repository\CityRepository;
use Symfony\Component\Uid\Uuid;

class CityService
{
    public const DEFAULT_LIMIT = 20;
    public const MAX_LIMIT = 100;

    // le repository n'est pas construit ici, il est demandé au conteneur
    public function __construct(
        private readonly CityRepository $cities,
    )
    {
    }

    public function toList(City $city): CityListOutput {
        return new CityListOutput($city->getId(), $city->getName());
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

}
