<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;

interface PackageRepositoryInterface
{
    public function all(): Collection;
    public function findById(string $id): ?Package;
}
