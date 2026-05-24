<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case SAR = 'SAR';
    case USD = 'USD';
    case EUR = 'EUR';
}
