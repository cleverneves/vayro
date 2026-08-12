<?php

namespace Database\Factories;

use App\Models\Carro;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Locacao>
 */
class LocacaoFactory extends Factory
{
    public function definition(): array
    {
        $inicio = $this->faker->dateTimeBetween('-1 month', 'now');

        return [
            'cliente_id' => Cliente::factory(),
            'carro_id' => Carro::factory(),
            'data_inicio_periodo' => $inicio,
            'data_final_previsto_periodo' => (clone $inicio)->modify('+5 days'),
            'data_final_realizado_periodo' => null,
            'valor_diaria' => $this->faker->randomFloat(2, 80, 400),
            'km_inicial' => $this->faker->numberBetween(0, 50000),
            'km_final' => null,
        ];
    }

    public function finalizada(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'data_final_realizado_periodo' => (clone $attributes['data_final_previsto_periodo']),
                'km_final' => $attributes['km_inicial'] + $this->faker->numberBetween(50, 500),
            ];
        });
    }
}
