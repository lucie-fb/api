<?php

namespace App\Dto\Trip;

use ApiPlatform\Metadata\ApiProperty;
use App\Dto\City\CityListOutput;
use Symfony\Component\Uid\Uuid;

// pas de `final` : TripDetailsOutput hérite de cette classe pour ajouter les champs du détail
class TripListOutput
{
    public function __construct(
        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'uuid',
            'description' => 'Identifiant du lancer.',
        ])]
        public readonly Uuid $id,

        #[ApiProperty(description: 'Ville de départ.')]
        public readonly CityListOutput $origin,

        #[ApiProperty(description: 'Ville d\'arrivée.')]
        public readonly CityListOutput $destination,

        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'date-time',
            'description' => 'Date et heure de départ.',
        ])]
        public readonly \DateTimeImmutable $departureAt,

        #[ApiProperty(schema: [
            'type' => 'integer',
            'description' => 'Durée du vol, en minutes.',
        ])]
        public readonly int $duration,

        #[ApiProperty(schema: [
            'type' => 'integer',
            'description' => 'Prix du billet, en centimes.',
        ])]
        public readonly int $price,
    ) {
    }
}
