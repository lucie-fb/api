<?php

namespace App\Dto\Trip;

use ApiPlatform\Metadata\ApiProperty;
use App\Dto\City\CityListOutput;
use Symfony\Component\Uid\Uuid;

readonly class TripListOutput
{
    public function __construct(

        #[ApiProperty(description: "Identifiant de la ville d'orgine")]
        public Uuid $id,

        #[ApiProperty(description: "Origine")]
        public CityListOutput $origin,

        #[ApiProperty(description: "Destination")]
        public CityListOutput $destination,

        #[ApiProperty(description: "Date de départ du voyage")]
        public \DateTimeImmutable $departureAt,

        #[ApiProperty(description: "Durée du voyage")]
        public int $duration,

        #[ApiProperty(description: "Prix du voyage")]
        public int $price,
    ) {
    }
}
