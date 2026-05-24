<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionPlan: string
{
    case TRIAL = 'trial';
    case PREMIUM = 'premium';
}
