<?php

namespace App\Dto\Cart;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class CartPayInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['card', 'voucher'])]
        #[ApiProperty(schema: [
            'type' => 'string',
            'enum' => ['card', 'voucher'],
            'description' => 'Moyen de paiement déclaré : card (carte) ou voucher (bon de transport de l\'Office).',
            'example' => 'card',
        ], required: true)]
        public string $paymentMethod,
    ) {
    }
}
