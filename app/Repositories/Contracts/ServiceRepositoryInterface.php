<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

interface ServiceRepositoryInterface
{
    public function allActive(): Collection;

    public function findById(string $id): ?Service;
}
