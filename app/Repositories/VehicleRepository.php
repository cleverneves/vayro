<?php

namespace App\Repositories;

use App\Models\Carro;
use Illuminate\Support\Collection;

class VehicleRepository
{
    public function availableInPeriod(string $startsOn, int $dayCount): Collection
    {
        return Carro::query()
            ->with(['modelo.marca'])
            ->whereDoesntHave('rentals', function ($query) use ($startsOn, $dayCount) {
                $query->reserving()->overlappingPeriod($startsOn, $dayCount);
            })
            ->orderBy('placa')
            ->get();
    }
}
