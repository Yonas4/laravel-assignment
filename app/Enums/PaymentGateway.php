<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentGateway: string
{
    case MOYASAR = 'moyasar';
    case STRIPE = 'stripe';
    case TAP = 'tap';
}
