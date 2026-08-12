<?php

namespace Database\Factories;

use App\Models\Modelo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Carro>
 */
class CarroFactory extends Factory
{
    public function definition(): array
    {
        return [
            'modelo_id' => Modelo::factory(),
            'placa' => strtoupper($this->faker->unique()->bothify('???-#?##')),
            'disponivel' => true,
            'km' => $this->faker->numberBetween(0, 100000),
        ];
    }

    public function indisponivel(): static
    {
        return $this->state(fn (array $attributes) => ['disponivel' => false]);
    }
}
