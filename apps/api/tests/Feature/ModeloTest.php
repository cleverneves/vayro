<?php

namespace Tests\Feature;

use App\Models\Carro;
use App\Models\Marca;
use App\Models\Modelo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModeloTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastra_modelo_valido(): void
    {
        Storage::fake('public');
        $marca = Marca::factory()->create();

        $response = $this->postJson('/api/v1/modelos', [
            'marca_id' => $marca->id,
            'nome' => 'Uno',
            'imagem' => UploadedFile::fake()->image('uno.png'),
            'numero_portas' => 4,
            'lugares' => 5,
            'air_bag' => true,
            'abs' => true,
        ], $this->authHeaders());

        $response->assertCreated()->assertJsonPath('data.nome', 'Uno');
    }

    public function test_nao_cadastra_modelo_com_marca_inexistente(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/api/v1/modelos', [
            'marca_id' => 999,
            'nome' => 'Uno',
            'imagem' => UploadedFile::fake()->image('uno.png'),
            'numero_portas' => 4,
            'lugares' => 5,
            'air_bag' => true,
            'abs' => true,
        ], $this->authHeaders());

        $response->assertStatus(422)->assertJsonValidationErrors(['marca_id']);
    }

    public function test_modelo_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/modelos/999', $this->authHeaders());

        $response->assertStatus(404);
    }

    public function test_nao_remove_modelo_com_carros_cadastrados(): void
    {
        $modelo = Modelo::factory()->create();
        Carro::factory()->create(['modelo_id' => $modelo->id]);

        $response = $this->deleteJson("/api/v1/modelos/{$modelo->id}", [], $this->authHeaders());

        $response->assertStatus(409);
    }
}
