<?php

namespace App\Dto\City;

use ApiPlatform\Metadata\ApiProperty;

class CityListOutput
{
    public function __construct(
        #[ApiProperty(description: 'identifiant unique de la ville')]
        public string $id,
        #[ApiProperty(description: 'nom unique de la ville')]
        public string $name,
    )
    {

    }
}
