<?php

namespace App\Repositories;

use App\Models\Carro;
use App\Models\Rental;
use Illuminate\Support\Collection;

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

    public function listForRenter(int $renterId): Collection
    {
        return Rental::query()
            ->with(['renter', 'vehicle.modelo.marca'])
            ->where('renter_id', $renterId)
            ->orderByDesc('requested_on')
            ->orderByDesc('id')
            ->get();
    }

    public function findForRenter(int $renterId, int $rentalId): ?Rental
    {
        return Rental::query()
            ->with(['renter', 'vehicle.modelo.marca'])
            ->where('renter_id', $renterId)
            ->whereKey($rentalId)
            ->first();
    }

    public function listAll(): Collection
    {
        return Rental::query()
            ->with(['renter', 'vehicle.modelo.marca'])
            ->orderByDesc('requested_on')
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $rentalId): ?Rental
    {
        return Rental::query()
            ->with(['renter', 'vehicle.modelo.marca'])
            ->whereKey($rentalId)
            ->first();
    }

    public function lockById(int $rentalId): ?Rental
    {
        return Rental::query()
            ->with(['renter', 'vehicle.modelo.marca'])
            ->whereKey($rentalId)
            ->lockForUpdate()
            ->first();
    }

    public function update(Rental $rental, array $attributes): Rental
    {
        $rental->update($attributes);

        return $rental->fresh(['renter', 'vehicle.modelo.marca']);
    }

    public function countRequestedOn(string $requestedOn): int
    {
        return Rental::query()->whereDate('requested_on', $requestedOn)->count();
    }
}
