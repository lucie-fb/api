<?php

namespace App\Dto\User;

use ApiPlatform\Metadata\ApiProperty;

final class UserDetailsOutput
{
    public function __construct(
        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'L\'identifiant de l\'utilisateur',
            'format' => 'uuid',
        ])]
        public readonly string $id,

        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'L\'email de l\'utilisateur',
            'format' => 'email',
        ])]
        public readonly string $email,

        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'Le prénom de l\'utilisateur',
            'nullable' => true,
        ])]
        public readonly null|string $firstName,

        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'Le nom de l\'utilisateur',
            'nullable' => true,
        ])]
        public readonly null|string $lastName,

        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'La date de création de l\'utilisateur',
            'format' => 'date-time',
        ])]
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }
}
