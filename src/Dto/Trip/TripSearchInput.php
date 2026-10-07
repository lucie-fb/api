<?php

namespace App\Dto\Trip;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class TripSearchInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'uuid',
            'description' => 'Identifiant de la ville de départ.',
        ], required: true)]
        public string $origin,

        #[Assert\NotBlank]
        #[Assert\Uuid]
        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'uuid',
            'description' => 'Identifiant de la ville d\'arrivée.',
        ], required: true)]
        public string $destination,

        #[Assert\NotBlank]
        #[Assert\Date]
        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'date',
            'description' => 'Jour du départ recherché, au format YYYY-MM-DD.',
        ], required: true)]
        public string $date,

        #[Assert\NotBlank]
        #[Assert\Positive]
        #[ApiProperty(schema: [
            'type' => 'integer',
            'description' => 'Nombre de passagers du voyage recherché.',
            'minimum' => 1,
        ], required: true)]
        public int $passengers,
    ) {
    }
}
