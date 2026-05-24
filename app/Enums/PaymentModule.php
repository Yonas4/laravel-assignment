<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentModule: string
{
    case SUBSCRIPTION = 'subscription';
    case BOOKING = 'booking';
    case CART = 'cart';
}
