<?php

namespace App\Entity\Enum;

enum CartStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
}
