<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'user_id' => null,
            'email' => null,
            'phone' => null,
        ];
    }
}
