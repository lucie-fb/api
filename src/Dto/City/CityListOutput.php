<?php

namespace App\Dto\City;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Uid\Uuid;

final class CityListOutput
{
    public function __construct(
        #[ApiProperty(schema: [
            'type' => 'string',
            'format' => 'uuid',
            'description' => 'Identifiant unique de la ville.',
        ])]
        public readonly Uuid $id,

        #[ApiProperty(schema: [
            'type' => 'string',
            'description' => 'Nom de la ville.',
        ])]
        public readonly string $name,
    ) {
    }
}
