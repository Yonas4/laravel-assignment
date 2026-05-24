<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function allAvailable(): Collection
    {
        return Service::where('is_available', true)->get();
    }

    public function findById(string $id): ?Service
    {
        return Service::find($id);
    }
}
