<?php

namespace Tests\Feature;

use App\Models\Carro;
use App\Models\Cliente;
use App\Models\Locacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_locacao_valida(): void
    {
        $cliente = Cliente::factory()->create();
        $carro = Carro::factory()->create();

        $response = $this->postJson('/api/v1/locacoes', [
            'cliente_id' => $cliente->id,
            'carro_id' => $carro->id,
            'data_inicio_periodo' => '2026-08-01 10:00:00',
            'data_final_previsto_periodo' => '2026-08-05 10:00:00',
            'valor_diaria' => 150.50,
            'km_inicial' => 1000,
        ], $this->authHeaders());

        $response->assertCreated()->assertJsonPath('data.km_inicial', 1000);
    }

    public function test_nao_registra_locacao_com_data_final_anterior_a_inicial(): void
    {
        $cliente = Cliente::factory()->create();
        $carro = Carro::factory()->create();

        $response = $this->postJson('/api/v1/locacoes', [
            'cliente_id' => $cliente->id,
            'carro_id' => $carro->id,
            'data_inicio_periodo' => '2026-08-05 10:00:00',
            'data_final_previsto_periodo' => '2026-08-01 10:00:00',
            'valor_diaria' => 150.50,
            'km_inicial' => 1000,
        ], $this->authHeaders());

        $response->assertStatus(422)->assertJsonValidationErrors(['data_final_previsto_periodo']);
    }

    public function test_filtra_locacoes_por_carro(): void
    {
        $carro = Carro::factory()->create();
        Locacao::factory()->create(['carro_id' => $carro->id]);
        Locacao::factory()->create();

        $response = $this->getJson("/api/v1/locacoes?carro_id={$carro->id}", $this->authHeaders());

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_locacao_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/locacoes/999', $this->authHeaders());

        $response->assertStatus(404);
    }

    public function test_finaliza_locacao_atualizando_km_final(): void
    {
        $locacao = Locacao::factory()->create();

        $response = $this->putJson("/api/v1/locacoes/{$locacao->id}", [
            'km_final' => $locacao->km_inicial + 200,
            'data_final_realizado_periodo' => now()->addDay()->toDateTimeString(),
        ], $this->authHeaders());

        $response->assertOk()->assertJsonPath('data.km_final', $locacao->km_inicial + 200);
    }

    public function test_remove_locacao(): void
    {
        $locacao = Locacao::factory()->create();

        $response = $this->deleteJson("/api/v1/locacoes/{$locacao->id}", [], $this->authHeaders());

        $response->assertNoContent();
    }
}
