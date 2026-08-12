<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Marca>
 */
class MarcaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => 'Marca ' . $this->faker->unique()->numberBetween(1, 999999),
            'imagem' => 'imagens/' . $this->faker->uuid() . '.png',
        ];
    }
}
