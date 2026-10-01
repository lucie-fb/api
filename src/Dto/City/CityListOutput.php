<?php

namespace App\Dto\City;

use Symfony\Component\Uid\Uuid;

final class CityListOutput
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
    ) {
    }
}
