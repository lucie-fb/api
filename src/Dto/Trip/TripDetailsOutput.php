<?php


namespace App\Dto\Trip;
use ApiPlatform\Metadata\ApiProperty;
use App\Dto\City\CityListOutput;
use Symfony\Component\Uid\Uuid;

final readonly class TripDetailsOutput extends TripListOutput {

    public function __construct(

        Uuid $id,
        CityListOutput $origin,
        CityListOutput $destination,
        \DateTimeImmutable $departureAt,
        int $duration,
        int $price,

        #[ApiProperty(description: "Poids maximale du baggage")]
        public ?int $maxBaggageWeightKg,

        #[ApiProperty(description: "Modèle de la catapulte")]
        public null|string $catapultModel,

        #[ApiProperty(description: "Tableau d'information du voyage")]
        public null|string $boardingInfo
    )
    {
        parent::__construct($id, $origin, $destination, $departureAt, $duration, $price);


    }

}
