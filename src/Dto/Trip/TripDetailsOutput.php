<?php

namespace App\Dto\Trip;

use ApiPlatform\Metadata\ApiProperty;
use App\Dto\City\CityListOutput;
use Symfony\Component\Uid\Uuid;

// un détail EST un résumé, plus ce qu'on ne montre qu'une fois : l'héritage dit ça du domaine
final class TripDetailsOutput extends TripListOutput
{
    public function __construct(
        Uuid $id,
        CityListOutput $origin,
        CityListOutput $destination,
        \DateTimeImmutable $departureAt,
        int $duration,
        int $price,

        #[ApiProperty(schema: [
            'type' => 'integer',
            'description' => 'Franchise de bagage, en kilogrammes.',
            'minimum' => 0,
        ])]
        public readonly int $maxBaggageWeightKg,

        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'Modèle de catapulte du lancer.',
        ])]
        public readonly string $catapultModel,

        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'Consignes d\'embarquement.',
        ])]
        public readonly string $boardingInfo,
    ) {
        // les six champs du résumé ne font que traverser : ils repartent tels quels au parent
        parent::__construct($id, $origin, $destination, $departureAt, $duration, $price);
    }
}
