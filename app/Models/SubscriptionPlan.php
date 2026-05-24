<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['slug', 'name', 'description', 'price', 'currency', 'duration_days', 'is_trial', 'features', 'is_active'])]
class SubscriptionPlan extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'is_trial' => 'boolean',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
