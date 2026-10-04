<?php

namespace Database\Factories;

use App\Models\Marca;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modelo>
 */
class ModeloFactory extends Factory
{
    public function definition(): array
    {
        return [
            'marca_id' => Marca::factory(),
            'nome' => $this->faker->unique()->word(),
            'imagem' => 'imagens/modelos/' . $this->faker->uuid() . '.png',
            'numero_portas' => $this->faker->numberBetween(2, 4),
            'lugares' => $this->faker->numberBetween(2, 7),
            'air_bag' => $this->faker->boolean(),
            'abs' => $this->faker->boolean(),
        ];
    }
}
