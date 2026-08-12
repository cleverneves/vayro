<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_consegue_autenticar_com_credenciais_validas(): void
    {
        $user = User::factory()->create(['password' => bcrypt('senha-correta')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'senha-correta',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_com_credenciais_invalidas_retorna_401(): void
    {
        $user = User::factory()->create(['password' => bcrypt('senha-correta')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_sem_email_retorna_erro_de_validacao(): void
    {
        $response = $this->postJson('/api/v1/login', ['password' => 'qualquer']);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_endpoint_protegido_sem_token_retorna_nao_autenticado(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_usuario_autenticado_consegue_ver_proprio_perfil(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/v1/me', $this->authHeaders($user));

        $response->assertOk()->assertJsonPath('data.email', $user->email);
    }
}
