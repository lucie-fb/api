<?php

namespace App\Dto\User;

use ApiPlatform\Metadata\ApiProperty;

final class UserDetailsOutput
{
    public function __construct(
        #[ApiProperty(description: "Identifiant du compte, un UUID v7 produit par l'entité.")]
        public string $id,
        #[ApiProperty(description: "Adresse de connexion du compte.")]
        public string $email,
        #[ApiProperty(description: "Prénom du voyageur. Nul quand il n'a pas été renseigné.")]
        public ?string $firstName,
        #[ApiProperty(description: "Nom du voyageur. Nul quand il n'a pas été renseigné.")]
        public ?string $lastName,
        #[ApiProperty(description: "Date de création du compte, estampillée à l'inscription.")]
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
