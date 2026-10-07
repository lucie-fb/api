<?php

namespace App\Dto\Ticket;

use ApiPlatform\Metadata\ApiProperty;
use App\Dto\Trip\TripListOutput;
use Symfony\Component\Uid\Uuid;

final class TicketListOutput
{
    public function __construct(
        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'uuid',
            'description' => 'Identifiant du billet.',
        ])]
        public readonly Uuid $id,

        #[ApiProperty(description: 'Le lancer sur lequel le billet donne droit d\'embarquer.')]
        public readonly TripListOutput $trip,

        #[ApiProperty(schema: [
            'type' => 'integer',
            'description' => 'Prix acquitté pour cette place, en centimes.',
        ])]
        public readonly int $price,

        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'date-time',
            'description' => 'Date d\'émission du billet.',
        ])]
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }
}
