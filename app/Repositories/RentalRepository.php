<?php

namespace App\Repositories;

use App\Models\Carro;
use App\Models\Rental;

class RentalRepository
{
    public function lockVehicle(int $vehicleId): ?Carro
    {
        return Carro::query()->whereKey($vehicleId)->lockForUpdate()->first();
    }

    public function hasOverlappingReservation(int $vehicleId, string $startsOn, int $dayCount): bool
    {
        return Rental::query()
            ->where('vehicle_id', $vehicleId)
            ->reserving()
            ->overlappingPeriod($startsOn, $dayCount)
            ->exists();
    }

    public function create(array $attributes): Rental
    {
        return Rental::query()->create($attributes);
    }
}
