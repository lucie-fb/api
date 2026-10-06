<?php

namespace App\Entity\Enum;
use Symfony\Component\Uid\Uuid;

enum CartStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';

}
