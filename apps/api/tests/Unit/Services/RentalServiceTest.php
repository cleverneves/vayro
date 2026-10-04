<?php

namespace Tests\Unit\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Carro;
use App\Models\Cliente;
use App\Models\Rental;
use App\Models\User;
use App\Repositories\RentalRepository;
use App\Repositories\RenterRepository;
use App\Repositories\VehicleRepository;
use App\Services\RentalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalServiceTest extends TestCase
{
    use RefreshDatabase;

    private RentalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RentalService(
            new VehicleRepository(),
            new RentalRepository(),
            new RenterRepository(),
        );
    }

    public function test_available_list_returns_only_free_vehicles_ordered_by_placa(): void
    {
        $actor = $this->renterActor();
        $today = $this->today();
        $bravo = Carro::factory()->indisponivel()->create(['placa' => 'BRA1A11']);
        $alfa = Carro::factory()->create(['placa' => 'ALF1A11']);
        $reserved = Carro::factory()->create(['placa' => 'RES1A11']);

        Rental::factory()->create([
            'vehicle_id' => $reserved->id,
            'starts_on' => $today,
            'day_count' => 2,
            'status' => Rental::STATUS_REQUESTED,
        ]);

        $available = $this->service->listAvailableVehicles($actor, $today, 2);

        $this->assertSame(['ALF1A11', 'BRA1A11'], $available->pluck('placa')->all());
        $this->assertTrue($available->contains('id', $alfa->id));
        $this->assertTrue($available->contains('id', $bravo->id));
        $this->assertFalse($available->contains('id', $reserved->id));
        $this->assertSame(1, Rental::query()->count());
    }

    public function test_available_list_is_empty_when_every_vehicle_is_reserved(): void
    {
        $actor = $this->renterActor();
        $today = $this->today();
        $vehicle = Carro::factory()->create();

        Rental::factory()->create([
            'vehicle_id' => $vehicle->id,
            'starts_on' => $today,
            'day_count' => 1,
            'status' => Rental::STATUS_CONFIRMED,
        ]);

        $available = $this->service->listAvailableVehicles($actor, $today, 1);

        $this->assertTrue($available->isEmpty());
        $this->assertSame(1, Rental::query()->count());
    }

    public function test_available_list_rejects_invalid_period(): void
    {
        $actor = $this->renterActor();

        foreach ([[$this->yesterday(), 1], [$this->today(), 0], [$this->today(), 91]] as [$startsOn, $dayCount]) {
            try {
                $this->service->listAvailableVehicles($actor, $startsOn, $dayCount);
                $this->fail("Expected period {$startsOn}/{$dayCount} to be rejected.");
            } catch (BusinessRuleException $e) {
                $this->assertSame(BusinessRuleException::INVALID_PERIOD, $e->domainCode());
            }
        }
    }

    public function test_create_requested_rental_with_optional_comment(): void
    {
        $actor = $this->renterActor();
        $vehicle = Carro::factory()->create();
        $today = $this->today();

        $withComment = $this->service->createForRenter($actor, [
            'vehicle_id' => $vehicle->id,
            'starts_on' => $today,
            'day_count' => 2,
            'reason' => Rental::REASON_TRIP,
            'comment' => 'Viagem a trabalho',
        ]);

        $this->assertSame(Rental::STATUS_REQUESTED, $withComment->status);
        $this->assertSame('Viagem a trabalho', $withComment->comment);
        $this->assertSame($today, $withComment->requested_on->toDateString());
        $this->assertSame($today, $withComment->starts_on->toDateString());
        $this->assertSame(2, $withComment->day_count);

        $otherVehicle = Carro::factory()->create();
        $withoutComment = $this->service->createForRenter($actor, [
            'vehicle_id' => $otherVehicle->id,
            'starts_on' => $today,
            'day_count' => 1,
            'reason' => Rental::REASON_LEISURE,
        ]);

        $this->assertNull($withoutComment->comment);

        $thirdVehicle = Carro::factory()->create();
        $emptyComment = $this->service->createForRenter($actor, [
            'vehicle_id' => $thirdVehicle->id,
            'starts_on' => $today,
            'day_count' => 1,
            'reason' => Rental::REASON_EVERYDAY,
            'comment' => '',
        ]);

        $this->assertNull($emptyComment->comment);
    }

    public function test_create_rejects_invalid_period_or_reason_without_inserting(): void
    {
        $actor = $this->renterActor();
        $vehicle = Carro::factory()->create();

        $invalid = [
            ['starts_on' => $this->yesterday(), 'day_count' => 1, 'reason' => Rental::REASON_TRIP, 'code' => BusinessRuleException::INVALID_PERIOD],
            ['starts_on' => $this->today(), 'day_count' => 0, 'reason' => Rental::REASON_TRIP, 'code' => BusinessRuleException::INVALID_PERIOD],
            ['starts_on' => $this->today(), 'day_count' => 91, 'reason' => Rental::REASON_TRIP, 'code' => BusinessRuleException::INVALID_PERIOD],
            ['starts_on' => $this->today(), 'day_count' => 1, 'reason' => 'viagem', 'code' => BusinessRuleException::INVALID_REASON],
        ];

        foreach ($invalid as $case) {
            try {
                $this->service->createForRenter($actor, [
                    'vehicle_id' => $vehicle->id,
                    'starts_on' => $case['starts_on'],
                    'day_count' => $case['day_count'],
                    'reason' => $case['reason'],
                ]);
                $this->fail('Expected invalid create to be rejected.');
            } catch (BusinessRuleException $e) {
                $this->assertSame($case['code'], $e->domainCode());
            }
        }

        $this->assertSame(0, Rental::query()->count());
    }

    public function test_one_day_rental_does_not_block_the_next_day(): void
    {
        $actor = $this->renterActor();
        $other = $this->renterActor();
        $vehicle = Carro::factory()->create();

        $this->service->createForRenter($actor, [
            'vehicle_id' => $vehicle->id,
            'starts_on' => $this->today(),
            'day_count' => 1,
            'reason' => Rental::REASON_TRIP,
        ]);

        $nextDay = $this->service->createForRenter($other, [
            'vehicle_id' => $vehicle->id,
            'starts_on' => $this->tomorrow(),
            'day_count' => 1,
            'reason' => Rental::REASON_TRIP,
        ]);

        $this->assertSame(Rental::STATUS_REQUESTED, $nextDay->status);
        $this->assertSame(2, Rental::query()->where('vehicle_id', $vehicle->id)->count());
    }

    public function test_two_day_rental_blocks_the_next_day(): void
    {
        $actor = $this->renterActor();
        $other = $this->renterActor();
        $vehicle = Carro::factory()->create();

        $this->service->createForRenter($actor, [
            'vehicle_id' => $vehicle->id,
            'starts_on' => $this->today(),
            'day_count' => 2,
            'reason' => Rental::REASON_TRIP,
        ]);

        try {
            $this->service->createForRenter($other, [
                'vehicle_id' => $vehicle->id,
                'starts_on' => $this->tomorrow(),
                'day_count' => 1,
                'reason' => Rental::REASON_TRIP,
            ]);
            $this->fail('Expected overlapping two-day rental to be rejected.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::VEHICLE_UNAVAILABLE, $e->domainCode());
        }

        $this->assertSame(1, Rental::query()->count());
    }

    public function test_cancelled_and_completed_do_not_reserve_the_vehicle(): void
    {
        $actor = $this->renterActor();
        $today = $this->today();
        $cancelledVehicle = Carro::factory()->create();
        $completedVehicle = Carro::factory()->create();

        Rental::factory()->create([
            'vehicle_id' => $cancelledVehicle->id,
            'starts_on' => $today,
            'day_count' => 2,
            'status' => Rental::STATUS_CANCELLED,
        ]);
        Rental::factory()->create([
            'vehicle_id' => $completedVehicle->id,
            'starts_on' => $today,
            'day_count' => 2,
            'status' => Rental::STATUS_COMPLETED,
        ]);

        $available = $this->service->listAvailableVehicles($actor, $today, 2);

        $this->assertTrue($available->contains('id', $cancelledVehicle->id));
        $this->assertTrue($available->contains('id', $completedVehicle->id));

        $created = $this->service->createForRenter($actor, [
            'vehicle_id' => $cancelledVehicle->id,
            'starts_on' => $today,
            'day_count' => 2,
            'reason' => Rental::REASON_TRIP,
        ]);

        $this->assertSame(Rental::STATUS_REQUESTED, $created->status);
    }

    public function test_reserving_statuses_block_the_vehicle(): void
    {
        $actor = $this->renterActor();
        $today = $this->today();

        foreach ([Rental::STATUS_REQUESTED, Rental::STATUS_CONFIRMED, Rental::STATUS_IN_PROGRESS] as $status) {
            $vehicle = Carro::factory()->create();
            Rental::factory()->create([
                'vehicle_id' => $vehicle->id,
                'starts_on' => $today,
                'day_count' => 1,
                'status' => $status,
            ]);

            try {
                $this->service->createForRenter($actor, [
                    'vehicle_id' => $vehicle->id,
                    'starts_on' => $today,
                    'day_count' => 1,
                    'reason' => Rental::REASON_TRIP,
                ]);
                $this->fail("Expected status {$status} to reserve the vehicle.");
            } catch (BusinessRuleException $e) {
                $this->assertSame(BusinessRuleException::VEHICLE_UNAVAILABLE, $e->domainCode());
            }
        }
    }

    public function test_concurrent_create_on_same_vehicle_accepts_only_one(): void
    {
        $firstActor = $this->renterActor();
        $secondActor = $this->renterActor();
        $vehicle = Carro::factory()->create();
        $payload = [
            'vehicle_id' => $vehicle->id,
            'starts_on' => $this->today(),
            'day_count' => 3,
            'reason' => Rental::REASON_TRIP,
        ];

        $accepted = $this->service->createForRenter($firstActor, $payload);

        try {
            $this->service->createForRenter($secondActor, $payload);
            $this->fail('Expected the second concurrent request to be refused.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::VEHICLE_UNAVAILABLE, $e->domainCode());
        }

        $this->assertSame(Rental::STATUS_REQUESTED, $accepted->status);
        $this->assertSame(1, Rental::query()->where('vehicle_id', $vehicle->id)->count());
    }

    public function test_create_rejects_missing_vehicle_and_non_renter(): void
    {
        $renter = $this->renterActor();
        $admin = User::factory()->create();

        try {
            $this->service->createForRenter($renter, [
                'vehicle_id' => 999,
                'starts_on' => $this->today(),
                'day_count' => 1,
                'reason' => Rental::REASON_TRIP,
            ]);
            $this->fail('Expected missing vehicle to be unavailable.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::VEHICLE_UNAVAILABLE, $e->domainCode());
        }

        try {
            $this->service->createForRenter($admin, [
                'vehicle_id' => Carro::factory()->create()->id,
                'starts_on' => $this->today(),
                'day_count' => 1,
                'reason' => Rental::REASON_TRIP,
            ]);
            $this->fail('Expected admin create to be forbidden.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::FORBIDDEN, $e->domainCode());
        }

        $this->assertSame(0, Rental::query()->count());
    }

    public function test_renter_lists_only_own_rentals_in_requested_order(): void
    {
        $owner = $this->renterActor();
        $other = $this->renterActor();
        $ownerId = $this->renterId($owner);

        $older = Rental::factory()->create([
            'renter_id' => $ownerId,
            'requested_on' => $this->yesterday(),
        ]);
        $newer = Rental::factory()->create([
            'renter_id' => $ownerId,
            'requested_on' => $this->today(),
        ]);
        $sameDayLater = Rental::factory()->create([
            'renter_id' => $ownerId,
            'requested_on' => $this->today(),
        ]);
        Rental::factory()->create([
            'renter_id' => $this->renterId($other),
            'requested_on' => $this->today(),
        ]);

        $list = $this->service->listForRenter($owner);

        $this->assertSame(
            [$sameDayLater->id, $newer->id, $older->id],
            $list->pluck('id')->all(),
        );
        $this->assertTrue($list->every(fn (Rental $rental) => $rental->renter_id === $ownerId));
    }

    public function test_renter_without_rentals_sees_empty_list_and_not_foreign_ones(): void
    {
        $empty = $this->renterActor();
        $other = $this->renterActor();
        Rental::factory()->create(['renter_id' => $this->renterId($other)]);

        $list = $this->service->listForRenter($empty);

        $this->assertTrue($list->isEmpty());
        $this->assertSame(1, Rental::query()->count());
    }

    public function test_renter_shows_own_rental_and_rejects_foreign_or_missing_id(): void
    {
        $owner = $this->renterActor();
        $other = $this->renterActor();
        $own = Rental::factory()->create(['renter_id' => $this->renterId($owner)]);
        $foreign = Rental::factory()->create(['renter_id' => $this->renterId($other)]);

        $shown = $this->service->showForRenter($owner, $own->id);

        $this->assertSame($own->id, $shown->id);
        $this->assertSame($this->renterId($owner), $shown->renter_id);

        foreach ([$foreign->id, 999] as $id) {
            try {
                $this->service->showForRenter($owner, $id);
                $this->fail("Expected rental {$id} to be not_found.");
            } catch (BusinessRuleException $e) {
                $this->assertSame(BusinessRuleException::NOT_FOUND, $e->domainCode());
                $this->assertSame('Locação não encontrada.', $e->getMessage());
            }
        }
    }

    public function test_admin_lists_all_rentals_of_any_status_and_date(): void
    {
        $admin = User::factory()->create();
        $first = $this->renterActor();
        $second = $this->renterActor();

        $older = Rental::factory()->create([
            'renter_id' => $this->renterId($first),
            'requested_on' => $this->yesterday(),
            'status' => Rental::STATUS_CANCELLED,
        ]);
        $newer = Rental::factory()->create([
            'renter_id' => $this->renterId($second),
            'requested_on' => $this->today(),
            'starts_on' => $this->tomorrow(),
            'status' => Rental::STATUS_REQUESTED,
        ]);

        $list = $this->service->listAll($admin);

        $this->assertSame([$newer->id, $older->id], $list->pluck('id')->all());
        $this->assertSame(2, $list->count());
    }

    public function test_admin_rental_list_is_empty_when_there_are_no_rentals(): void
    {
        $this->assertTrue($this->service->listAll(User::factory()->create())->isEmpty());
    }

    public function test_admin_shows_any_rental_and_rejects_missing_id(): void
    {
        $admin = User::factory()->create();
        $rental = Rental::factory()->create([
            'renter_id' => $this->renterId($this->renterActor()),
            'status' => Rental::STATUS_COMPLETED,
        ]);

        $shown = $this->service->showForAdmin($admin, $rental->id);

        $this->assertSame($rental->id, $shown->id);
        $this->assertSame(Rental::STATUS_COMPLETED, $shown->status);

        try {
            $this->service->showForAdmin($admin, 999);
            $this->fail('Expected missing rental to be not_found.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::NOT_FOUND, $e->domainCode());
            $this->assertSame('Locação não encontrada.', $e->getMessage());
        }
    }

    public function test_renter_is_forbidden_from_admin_rental_queries(): void
    {
        $renter = $this->renterActor();
        $rental = Rental::factory()->create(['renter_id' => $this->renterId($renter)]);

        foreach (['list', 'show'] as $action) {
            try {
                if ($action === 'list') {
                    $this->service->listAll($renter);
                } else {
                    $this->service->showForAdmin($renter, $rental->id);
                }
                $this->fail('Expected renter admin rental query to be forbidden.');
            } catch (BusinessRuleException $e) {
                $this->assertSame(BusinessRuleException::FORBIDDEN, $e->domainCode());
            }
        }
    }

    public function test_admin_follows_allowed_transitions_and_owner_sees_the_same_state(): void
    {
        $admin = User::factory()->create();
        $owner = $this->renterActor();
        $rental = Rental::factory()->create([
            'renter_id' => $this->renterId($owner),
            'status' => Rental::STATUS_REQUESTED,
            'admin_note' => null,
        ]);

        $confirmed = $this->service->updateByAdmin($admin, $rental->id, [
            'status' => Rental::STATUS_CONFIRMED,
            'admin_note' => 'Documentos conferidos',
        ]);
        $this->assertSame(Rental::STATUS_CONFIRMED, $confirmed->status);
        $this->assertSame('Documentos conferidos', $confirmed->admin_note);

        $inProgress = $this->service->updateByAdmin($admin, $rental->id, [
            'status' => Rental::STATUS_IN_PROGRESS,
        ]);
        $this->assertSame(Rental::STATUS_IN_PROGRESS, $inProgress->status);
        $this->assertSame('Documentos conferidos', $inProgress->admin_note);

        $completed = $this->service->updateByAdmin($admin, $rental->id, [
            'status' => Rental::STATUS_COMPLETED,
        ]);
        $this->assertSame(Rental::STATUS_COMPLETED, $completed->status);

        $asOwner = $this->service->showForRenter($owner, $rental->id);
        $this->assertSame(Rental::STATUS_COMPLETED, $asOwner->status);
        $this->assertSame('Documentos conferidos', $asOwner->admin_note);
    }

    public function test_cancelled_releases_the_vehicle_for_a_new_rental(): void
    {
        $admin = User::factory()->create();
        $owner = $this->renterActor();
        $other = $this->renterActor();
        $vehicle = Carro::factory()->create();
        $today = $this->today();

        $rental = $this->service->createForRenter($owner, [
            'vehicle_id' => $vehicle->id,
            'starts_on' => $today,
            'day_count' => 2,
            'reason' => Rental::REASON_TRIP,
        ]);

        $this->service->updateByAdmin($admin, $rental->id, [
            'status' => Rental::STATUS_CANCELLED,
        ]);

        $replacement = $this->service->createForRenter($other, [
            'vehicle_id' => $vehicle->id,
            'starts_on' => $today,
            'day_count' => 2,
            'reason' => Rental::REASON_LEISURE,
        ]);

        $this->assertSame(Rental::STATUS_CANCELLED, $rental->fresh()->status);
        $this->assertSame(Rental::STATUS_REQUESTED, $replacement->status);
    }

    public function test_admin_can_change_or_clear_note_without_changing_status(): void
    {
        $admin = User::factory()->create();
        $rental = Rental::factory()->create([
            'renter_id' => $this->renterId($this->renterActor()),
            'status' => Rental::STATUS_CONFIRMED,
            'admin_note' => 'Primeira nota',
        ]);

        $updated = $this->service->updateByAdmin($admin, $rental->id, [
            'admin_note' => 'Nota atualizada',
        ]);
        $this->assertSame(Rental::STATUS_CONFIRMED, $updated->status);
        $this->assertSame('Nota atualizada', $updated->admin_note);

        $cleared = $this->service->updateByAdmin($admin, $rental->id, [
            'admin_note' => '',
        ]);
        $this->assertSame(Rental::STATUS_CONFIRMED, $cleared->status);
        $this->assertNull($cleared->admin_note);
    }

    public function test_illegal_transition_does_not_save_the_note(): void
    {
        $admin = User::factory()->create();
        $rental = Rental::factory()->create([
            'renter_id' => $this->renterId($this->renterActor()),
            'status' => Rental::STATUS_REQUESTED,
            'admin_note' => 'Original',
        ]);

        try {
            $this->service->updateByAdmin($admin, $rental->id, [
                'status' => Rental::STATUS_COMPLETED,
                'admin_note' => 'Não deve gravar',
            ]);
            $this->fail('Expected illegal transition to be rejected.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::INVALID_TRANSITION, $e->domainCode());
        }

        $rental->refresh();
        $this->assertSame(Rental::STATUS_REQUESTED, $rental->status);
        $this->assertSame('Original', $rental->admin_note);
    }

    public function test_closed_rental_rejects_status_and_note_changes(): void
    {
        $admin = User::factory()->create();

        foreach ([Rental::STATUS_COMPLETED, Rental::STATUS_CANCELLED] as $status) {
            $rental = Rental::factory()->create([
                'renter_id' => $this->renterId($this->renterActor()),
                'status' => $status,
                'admin_note' => 'Final',
            ]);

            try {
                $this->service->updateByAdmin($admin, $rental->id, [
                    'admin_note' => 'Tentativa',
                ]);
                $this->fail("Expected {$status} note update to be rejected.");
            } catch (BusinessRuleException $e) {
                $this->assertSame(BusinessRuleException::RENTAL_CLOSED, $e->domainCode());
            }

            $this->assertSame('Final', $rental->fresh()->admin_note);
            $this->assertSame($status, $rental->fresh()->status);
        }
    }

    public function test_renter_cannot_update_rental_and_missing_id_is_not_found(): void
    {
        $owner = $this->renterActor();
        $rental = Rental::factory()->create([
            'renter_id' => $this->renterId($owner),
            'status' => Rental::STATUS_REQUESTED,
        ]);

        try {
            $this->service->updateByAdmin($owner, $rental->id, [
                'status' => Rental::STATUS_CANCELLED,
            ]);
            $this->fail('Expected renter update to be forbidden.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::FORBIDDEN, $e->domainCode());
        }

        try {
            $this->service->updateByAdmin(User::factory()->create(), 999, [
                'status' => Rental::STATUS_CONFIRMED,
            ]);
            $this->fail('Expected missing rental update to be not_found.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::NOT_FOUND, $e->domainCode());
        }

        $this->assertSame(Rental::STATUS_REQUESTED, $rental->fresh()->status);
    }

    public function test_daily_count_includes_cancelled_and_future_start_and_excludes_other_days(): void
    {
        $admin = User::factory()->create();
        $today = $this->today();
        $tomorrow = $this->tomorrow();
        $yesterday = $this->yesterday();

        Rental::factory()->create([
            'requested_on' => $today,
            'starts_on' => $today,
            'status' => Rental::STATUS_CANCELLED,
        ]);
        Rental::factory()->create([
            'requested_on' => $today,
            'starts_on' => $tomorrow,
            'status' => Rental::STATUS_REQUESTED,
        ]);
        Rental::factory()->create([
            'requested_on' => $yesterday,
            'starts_on' => $today,
            'day_count' => 2,
            'status' => Rental::STATUS_CONFIRMED,
        ]);

        $summary = $this->service->countRequestedOnCurrentDay($admin);

        $this->assertSame($today, $summary['date']);
        $this->assertSame(2, $summary['count']);
    }

    public function test_daily_count_is_zero_when_nothing_was_requested_today(): void
    {
        $admin = User::factory()->create();

        Rental::factory()->create([
            'requested_on' => $this->yesterday(),
            'starts_on' => $this->today(),
        ]);

        $summary = $this->service->countRequestedOnCurrentDay($admin);

        $this->assertSame($this->today(), $summary['date']);
        $this->assertSame(0, $summary['count']);
    }

    public function test_renter_cannot_see_daily_count(): void
    {
        Rental::factory()->create([
            'requested_on' => $this->today(),
        ]);

        try {
            $this->service->countRequestedOnCurrentDay($this->renterActor());
            $this->fail('Expected renter daily count to be forbidden.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::FORBIDDEN, $e->domainCode());
        }
    }

    private function renterActor(): User
    {
        $user = User::factory()->renter()->create();
        Cliente::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'nome' => $user->name,
            'phone' => '11999990000',
        ]);

        return $user->fresh();
    }

    private function renterId(User $actor): int
    {
        return (int) Cliente::query()->where('user_id', $actor->id)->value('id');
    }

    private function today(): string
    {
        return now(config('rental.timezone'))->toDateString();
    }

    private function tomorrow(): string
    {
        return now(config('rental.timezone'))->addDay()->toDateString();
    }

    private function yesterday(): string
    {
        return now(config('rental.timezone'))->subDay()->toDateString();
    }
}
