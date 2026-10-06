<?php

namespace App\Dto\Cart;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class CartAddLineInput
{
    #[ApiProperty(description: "Identifiant de lancer à ajouter.")]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $tripId;

    #[ApiProperty(description: "Nombre de places. Chaque place donnera un billet au paiement")]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $passengers = 1;
}
