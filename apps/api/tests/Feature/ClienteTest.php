<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Locacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastra_cliente_valido(): void
    {
        $response = $this->postJson('/api/v1/clientes', [
            'nome' => 'João da Silva',
        ], $this->authHeaders());

        $response->assertCreated()->assertJsonPath('data.nome', 'João da Silva');
    }

    public function test_nao_cadastra_cliente_com_nome_curto(): void
    {
        $response = $this->postJson('/api/v1/clientes', [
            'nome' => 'Jo',
        ], $this->authHeaders());

        $response->assertStatus(422)->assertJsonValidationErrors(['nome']);
    }

    public function test_cliente_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/clientes/999', $this->authHeaders());

        $response->assertStatus(404);
    }

    public function test_remove_cliente_sem_locacoes(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->deleteJson("/api/v1/clientes/{$cliente->id}", [], $this->authHeaders());

        $response->assertNoContent();
    }

    public function test_nao_remove_cliente_com_locacoes_cadastradas(): void
    {
        $cliente = Cliente::factory()->create();
        Locacao::factory()->create(['cliente_id' => $cliente->id]);

        $response = $this->deleteJson("/api/v1/clientes/{$cliente->id}", [], $this->authHeaders());

        $response->assertStatus(409);
    }
}
