<?php

namespace Database\Factories;

use App\Models\Carro;
use App\Models\Cliente;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rental>
 */
class RentalFactory extends Factory
{
    public function definition(): array
    {
        $today = now(config('rental.timezone'))->toDateString();

        return [
            'renter_id' => Cliente::factory(),
            'vehicle_id' => Carro::factory(),
            'starts_on' => $today,
            'day_count' => 1,
            'reason' => 'trip',
            'comment' => null,
            'status' => Rental::STATUS_REQUESTED,
            'admin_note' => null,
            'requested_on' => $today,
        ];
    }
}
