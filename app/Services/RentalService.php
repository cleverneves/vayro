<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Cliente;
use App\Models\Rental;
use App\Models\User;
use App\Repositories\RentalRepository;
use App\Repositories\RenterRepository;
use App\Repositories\VehicleRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RentalService
{
    public function __construct(
        private readonly VehicleRepository $vehicles,
        private readonly RentalRepository $rentals,
        private readonly RenterRepository $renters,
    ) {
    }

    public function listAvailableVehicles(User $actor, string $startsOn, int $dayCount): Collection
    {
        $this->assertRenter($actor);
        $this->assertValidPeriod($startsOn, $dayCount);

        return $this->vehicles->availableInPeriod($startsOn, $dayCount);
    }

    public function createForRenter(User $actor, array $data): Rental
    {
        $renter = $this->requireRenter($actor);

        $startsOn = $data['starts_on'];
        $dayCount = (int) $data['day_count'];
        $this->assertValidPeriod($startsOn, $dayCount);
        $this->assertValidReason($data['reason']);

        $comment = $data['comment'] ?? null;
        if ($comment === '') {
            $comment = null;
        }

        return DB::transaction(function () use ($renter, $data, $startsOn, $dayCount, $comment) {
            $vehicle = $this->rentals->lockVehicle((int) $data['vehicle_id']);

            if ($vehicle === null || $this->rentals->hasOverlappingReservation($vehicle->id, $startsOn, $dayCount)) {
                throw new BusinessRuleException(
                    BusinessRuleException::VEHICLE_UNAVAILABLE,
                    'O automóvel não está disponível nesse período.',
                );
            }

            return $this->rentals->create([
                'renter_id' => $renter->id,
                'vehicle_id' => $vehicle->id,
                'starts_on' => $startsOn,
                'day_count' => $dayCount,
                'reason' => $data['reason'],
                'comment' => $comment,
                'status' => Rental::STATUS_REQUESTED,
                'admin_note' => null,
                'requested_on' => now(config('rental.timezone'))->toDateString(),
            ]);
        });
    }

    public function listForRenter(User $actor): Collection
    {
        $renter = $this->requireRenter($actor);

        return $this->rentals->listForRenter($renter->id);
    }

    public function showForRenter(User $actor, int $rentalId): Rental
    {
        $renter = $this->requireRenter($actor);
        $rental = $this->rentals->findForRenter($renter->id, $rentalId);

        if ($rental === null) {
            throw new BusinessRuleException(
                BusinessRuleException::NOT_FOUND,
                'Locação não encontrada.',
            );
        }

        return $rental;
    }

    public function listAll(User $actor): Collection
    {
        $this->assertAdmin($actor);

        return $this->rentals->listAll();
    }

    public function showForAdmin(User $actor, int $rentalId): Rental
    {
        $this->assertAdmin($actor);

        $rental = $this->rentals->findById($rentalId);
        if ($rental === null) {
            throw new BusinessRuleException(
                BusinessRuleException::NOT_FOUND,
                'Locação não encontrada.',
            );
        }

        return $rental;
    }

    private function requireRenter(User $actor): Cliente
    {
        $this->assertRenter($actor);

        $renter = $this->renters->findByUserId($actor->id);
        if ($renter === null) {
            throw new BusinessRuleException(
                BusinessRuleException::NOT_FOUND,
                'Locatário não encontrado.',
            );
        }

        return $renter;
    }

    private function assertRenter(User $actor): void
    {
        if ($actor->role !== 'renter') {
            throw new BusinessRuleException(
                BusinessRuleException::FORBIDDEN,
                'Acesso recusado.',
            );
        }
    }

    private function assertAdmin(User $actor): void
    {
        if ($actor->role !== 'admin') {
            throw new BusinessRuleException(
                BusinessRuleException::FORBIDDEN,
                'Acesso recusado.',
            );
        }
    }

    private function assertValidPeriod(string $startsOn, int $dayCount): void
    {
        $timezone = config('rental.timezone');
        $today = now($timezone)->startOfDay();
        $start = Carbon::parse($startsOn, $timezone)->startOfDay();

        if ($start->lt($today) || $dayCount < 1 || $dayCount > 90) {
            throw new BusinessRuleException(
                BusinessRuleException::INVALID_PERIOD,
                'O período informado não é válido.',
            );
        }
    }

    private function assertValidReason(string $reason): void
    {
        if (!in_array($reason, Rental::REASONS, true)) {
            throw new BusinessRuleException(
                BusinessRuleException::INVALID_REASON,
                'O motivo informado não é válido.',
            );
        }
    }
}
