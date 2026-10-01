<?php

namespace App\Dto\User;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class UserRegisterInput
{
    #[ApiProperty(description: "Adresse de connexion du compte à créer. Deux comptes ne peuvent pas la partager.")]
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[ApiProperty(description: "Mot de passe en clair, haché avant enregistrement. Il ne ressort jamais, ni dans une réponse ni dans les journaux.")]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public ?string $password = null;

    #[ApiProperty(description: "Prénom du voyageur. Facultatif.")]
    public ?string $firstName = null;

    #[ApiProperty(description: "Nom du voyageur. Facultatif.")]
    public ?string $lastName = null;
}
