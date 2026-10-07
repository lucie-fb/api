<?php

namespace App\Service;

use App\Dto\Trip\TripDetailsOutput;
use App\Dto\Trip\TripListOutput;
use App\Dto\Trip\TripSearchInput;
use App\Entity\Trip;
use App\Exception\City\CityNotFoundException;
use App\Exception\Trip\TripNotFoundException;
use App\Repository\TripRepository;
use Symfony\Component\Uid\Uuid;

class TripService
{
    // ni le repository ni le service des villes ne sont construits ici, ils sont demandés au conteneur
    public function __construct(
        private readonly TripRepository $tripRepository,
        private readonly CityService $cityService,
    ) {
    }

    /**
     * Returns the trips flying the submitted route on the submitted day.
     *
     * @return Trip[]
     *
     * @throws CityNotFoundException when either end of the route carries no city
     */
    public function search(TripSearchInput $input): array
    {
        // résoudre une ville appartient au domaine des villes : ce service passe par le sien, pas par son repository
        $origin = $this->cityService->findOneById(Uuid::fromString($input->origin));
        $destination = $this->cityService->findOneById(Uuid::fromString($input->destination));

        // la validation a garanti la forme de la date : la conversion arrive après, ici
        $day = new \DateTimeImmutable($input->date);

        return $this->tripRepository->search($origin, $destination, $day);
    }

    /**
     * Maps a trip onto the payload served by the search endpoint.
     */
    public function toList(Trip $trip): TripListOutput
    {
        return new TripListOutput(
            id: $trip->getId(),
            // la transformation d'une ville reste au domaine des villes, des deux côtés du trajet
            origin: $this->cityService->toList($trip->getOrigin()),
            destination: $this->cityService->toList($trip->getDestination()),
            departureAt: $trip->getDepartureAt(),
            duration: $trip->getDuration(),
            price: $trip->getPrice(),
        );
    }

    /**
     * Returns the trip carrying this identifier.
     *
     * @throws TripNotFoundException when no trip carries this identifier
     */
    public function findOneById(Uuid $id): Trip
    {
        // find() est héritée de Doctrine : rien à écrire dans le repository pour un accès par clé
        $trip = $this->tripRepository->find($id);

        if (null === $trip) {
            throw new TripNotFoundException();
        }

        return $trip;
    }

    /**
     * Maps a trip onto the payload served by the item endpoint.
     */
    public function toDetails(Trip $trip): TripDetailsOutput
    {
        return new TripDetailsOutput(
            id: $trip->getId(),
            origin: $this->cityService->toList($trip->getOrigin()),
            destination: $this->cityService->toList($trip->getDestination()),
            departureAt: $trip->getDepartureAt(),
            duration: $trip->getDuration(),
            price: $trip->getPrice(),
            // la franchise ne vient d'aucune colonne : c'est le modèle de catapulte qui la décide
            maxBaggageWeightKg: $trip->getCatapultModel()->maxBaggageWeightKg(),
            // le cas d'enum sort par sa valeur de chaîne, pas par son nom de cas
            catapultModel: $trip->getCatapultModel()->value,
            boardingInfo: $trip->getBoardingInfo(),
        );
    }
}
