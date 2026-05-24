<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Package;
use App\Repositories\Contracts\PackageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PackageRepository implements PackageRepositoryInterface
{
    public function all(): Collection
    {
        return Package::with('services')->get();
    }

    public function findById(string $id): ?Package
    {
        return Package::with('services')->find($id);
    }
}
