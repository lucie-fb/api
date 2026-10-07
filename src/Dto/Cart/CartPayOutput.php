<?php

namespace App\Dto\Cart;

use ApiPlatform\Metadata\ApiProperty;
use App\Dto\Ticket\TicketListOutput;

final class CartPayOutput
{
    /**
     * @param TicketListOutput[] $tickets
     */
    public function __construct(
        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'Référence de confirmation, à conserver.',
            'example' => 'ONTB-2026-4F2A9C',
        ])]
        public readonly string $confirmation,

        #[ApiProperty(description: 'Billets émis, un par place réservée.')]
        public readonly array $tickets,
    ) {
    }
}
