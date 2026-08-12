<?php

namespace Tests\Feature;

use App\Models\Marca;
use App\Models\Modelo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarcaTest extends TestCase
{
    use RefreshDatabase;

    public function test_listagem_de_marcas_exige_autenticacao(): void
    {
        $response = $this->getJson('/api/v1/marcas');

        $response->assertStatus(401);
    }

    public function test_lista_marcas_paginadas(): void
    {
        Marca::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/marcas', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_cadastra_marca_com_imagem_valida(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/api/v1/marcas', [
            'nome' => 'Fiat',
            'imagem' => UploadedFile::fake()->image('fiat.png'),
        ], $this->authHeaders());

        $response->assertCreated()->assertJsonPath('data.nome', 'Fiat');
        $this->assertDatabaseHas('marcas', ['nome' => 'Fiat']);
    }

    public function test_nao_cadastra_marca_sem_imagem(): void
    {
        $response = $this->postJson('/api/v1/marcas', [
            'nome' => 'Fiat',
        ], $this->authHeaders());

        $response->assertStatus(422)->assertJsonValidationErrors(['imagem']);
    }

    public function test_nao_cadastra_marca_com_nome_duplicado(): void
    {
        Storage::fake('public');
        Marca::factory()->create(['nome' => 'Fiat']);

        $response = $this->postJson('/api/v1/marcas', [
            'nome' => 'Fiat',
            'imagem' => UploadedFile::fake()->image('fiat.png'),
        ], $this->authHeaders());

        $response->assertStatus(422)->assertJsonValidationErrors(['nome']);
    }

    public function test_exibe_marca_existente_com_modelos(): void
    {
        $marca = Marca::factory()->create();
        Modelo::factory()->create(['marca_id' => $marca->id]);

        $response = $this->getJson("/api/v1/marcas/{$marca->id}", $this->authHeaders());

        $response->assertOk()->assertJsonCount(1, 'data.modelos');
    }

    public function test_marca_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/marcas/999', $this->authHeaders());

        $response->assertStatus(404);
    }

    public function test_atualiza_marca(): void
    {
        $marca = Marca::factory()->create();

        $response = $this->putJson("/api/v1/marcas/{$marca->id}", [
            'nome' => 'Novo Nome',
        ], $this->authHeaders());

        $response->assertOk()->assertJsonPath('data.nome', 'Novo Nome');
    }

    public function test_remove_marca_sem_modelos(): void
    {
        Storage::fake('public');
        $marca = Marca::factory()->create();

        $response = $this->deleteJson("/api/v1/marcas/{$marca->id}", [], $this->authHeaders());

        $response->assertNoContent();
        $this->assertDatabaseMissing('marcas', ['id' => $marca->id]);
    }

    public function test_nao_remove_marca_com_modelos_cadastrados(): void
    {
        $marca = Marca::factory()->create();
        Modelo::factory()->create(['marca_id' => $marca->id]);

        $response = $this->deleteJson("/api/v1/marcas/{$marca->id}", [], $this->authHeaders());

        $response->assertStatus(409);
        $this->assertDatabaseHas('marcas', ['id' => $marca->id]);
    }
}
