<?php

namespace App\Dto\Cart;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class CartAddLineInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'uuid',
            'description' => 'Identifiant du lancer à ajouter.',
        ], required: true)]
        public string $tripId,

        #[Assert\NotBlank]
        #[Assert\Positive]
        #[ApiProperty(schema: [
            'type' => 'integer',
            'description' => 'Nombre de places. Chaque place donnera un billet au paiement.',
            'minimum' => 1,
        ], required: true)]
        public int $passengers,
    ) {
    }
}
