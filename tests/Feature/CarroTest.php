<?php

namespace Tests\Feature;

use App\Models\Carro;
use App\Models\Locacao;
use App\Models\Modelo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarroTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_carros_paginados(): void
    {
        Carro::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/carros', $this->authHeaders());

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_filtra_carros_disponiveis(): void
    {
        Carro::factory()->create(['disponivel' => true]);
        Carro::factory()->indisponivel()->create();

        $response = $this->getJson('/api/v1/carros?disponivel=1', $this->authHeaders());

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_cadastra_carro_valido(): void
    {
        $modelo = Modelo::factory()->create();

        $response = $this->postJson('/api/v1/carros', [
            'modelo_id' => $modelo->id,
            'placa' => 'ABC-1234',
            'disponivel' => true,
            'km' => 0,
        ], $this->authHeaders());

        $response->assertCreated()->assertJsonPath('data.placa', 'ABC-1234');
    }

    public function test_nao_cadastra_carro_com_placa_duplicada(): void
    {
        $existente = Carro::factory()->create(['placa' => 'ABC-1234']);

        $response = $this->postJson('/api/v1/carros', [
            'modelo_id' => $existente->modelo_id,
            'placa' => 'ABC-1234',
            'disponivel' => true,
            'km' => 0,
        ], $this->authHeaders());

        $response->assertStatus(422)->assertJsonValidationErrors(['placa']);
    }

    public function test_carro_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/carros/999', $this->authHeaders());

        $response->assertStatus(404);
    }

    public function test_nao_remove_carro_com_locacoes_cadastradas(): void
    {
        $carro = Carro::factory()->create();
        Locacao::factory()->create(['carro_id' => $carro->id]);

        $response = $this->deleteJson("/api/v1/carros/{$carro->id}", [], $this->authHeaders());

        $response->assertStatus(409);
    }
}
