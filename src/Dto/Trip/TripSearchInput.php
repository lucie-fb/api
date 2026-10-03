<?php

namespace App\Dto\Trip;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class TripSearchInput
{
    #[ApiProperty(description: "Identifiant de la ville de départ.")]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public ?string $origin = null;

    #[ApiProperty(description: "Identifiant de la ville d'arrivée.")]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public ?string $destination = null;

    #[ApiProperty(description: "Jour du départ recherché, au format YYYY-MM-DD.")]
    #[Assert\NotBlank]
    #[Assert\Date]
    public ?string $date = null;

    #[ApiProperty(description: "Nombre de passagers du voyage recherché.")]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $passengers = null;
}
