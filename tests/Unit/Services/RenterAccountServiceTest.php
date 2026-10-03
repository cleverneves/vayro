<?php

namespace Tests\Unit\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Cliente;
use App\Models\Rental;
use App\Models\User;
use App\Repositories\RenterRepository;
use App\Services\RenterAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RenterAccountServiceTest extends TestCase
{
    use RefreshDatabase;

    private RenterAccountService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RenterAccountService(new RenterRepository());
    }

    public function test_register_creates_renter_account_and_profile(): void
    {
        $renter = $this->service->register($this->registration([
            'name' => 'Ana Lima',
            'email' => 'Ana@Example.com',
            'phone' => '11999990000',
            'password' => 'segredo12',
        ]));

        $this->assertDatabaseHas('users', [
            'id' => $renter->user_id,
            'name' => 'Ana Lima',
            'email' => 'ana@example.com',
            'role' => 'renter',
        ]);
        $this->assertDatabaseHas('clientes', [
            'id' => $renter->id,
            'nome' => 'Ana Lima',
            'email' => 'ana@example.com',
            'phone' => '11999990000',
            'user_id' => $renter->user_id,
        ]);
        $this->assertTrue(Hash::check('segredo12', User::query()->findOrFail($renter->user_id)->password));
    }

    public function test_register_rejects_email_already_used_by_another_account(): void
    {
        $this->service->register($this->registration());

        try {
            $this->service->register($this->registration([
                'name' => 'Outra Pessoa',
                'phone' => '11988887777',
                'password' => 'outrasenha',
            ]));
            $this->fail('Expected duplicate email to be rejected.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::EMAIL_TAKEN, $e->domainCode());
        }

        $this->assertSame(1, User::query()->count());
        $this->assertSame(1, Cliente::query()->count());
    }

    public function test_register_rejects_email_already_used_by_user_or_legacy_cliente(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);
        Cliente::factory()->create(['email' => 'legado@example.com']);

        foreach (['admin@example.com', 'legado@example.com'] as $email) {
            try {
                $this->service->register($this->registration([
                    'email' => $email,
                    'name' => 'Novo Locatário',
                ]));
                $this->fail("Expected {$email} to be rejected.");
            } catch (BusinessRuleException $e) {
                $this->assertSame(BusinessRuleException::EMAIL_TAKEN, $e->domainCode());
            }
        }

        $this->assertSame(0, User::query()->where('role', 'renter')->count());
        $this->assertSame(0, Cliente::query()->whereNotNull('user_id')->count());
    }

    public function test_update_profile_changes_own_name_email_and_phone(): void
    {
        $renter = $this->service->register($this->registration());
        $rental = Rental::factory()->create(['renter_id' => $renter->id]);
        $actor = User::query()->findOrFail($renter->user_id);

        $updated = $this->service->updateProfile($actor, $renter->id, [
            'name' => 'Ana Souza',
            'email' => 'Ana.Souza@Example.com',
            'phone' => '11911112222',
        ]);

        $this->assertSame('Ana Souza', $updated->nome);
        $this->assertSame('ana.souza@example.com', $updated->email);
        $this->assertSame('11911112222', $updated->phone);
        $this->assertDatabaseHas('users', [
            'id' => $actor->id,
            'name' => 'Ana Souza',
            'email' => 'ana.souza@example.com',
            'role' => 'renter',
        ]);
        $this->assertTrue(Hash::check('segredo12', $actor->fresh()->password));
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
            'renter_id' => $renter->id,
        ]);
    }

    public function test_update_profile_rejects_email_taken_by_another_renter(): void
    {
        $this->service->register($this->registration(['email' => 'primeiro@example.com']));
        $second = $this->service->register($this->registration([
            'name' => 'Bruno Lima',
            'email' => 'bruno@example.com',
            'phone' => '11977776666',
        ]));
        $actor = User::query()->findOrFail($second->user_id);

        try {
            $this->service->updateProfile($actor, $second->id, [
                'email' => 'primeiro@example.com',
            ]);
            $this->fail('Expected taken email change to be rejected.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::EMAIL_TAKEN, $e->domainCode());
        }

        $this->assertSame('bruno@example.com', $second->fresh()->email);
    }

    public function test_update_profile_rejects_foreign_renter_and_keeps_their_data(): void
    {
        $first = $this->service->register($this->registration([
            'name' => 'Ana Lima',
            'email' => 'ana@example.com',
        ]));
        $second = $this->service->register($this->registration([
            'name' => 'Bruno Lima',
            'email' => 'bruno@example.com',
            'phone' => '11977776666',
        ]));
        $actor = User::query()->findOrFail($second->user_id);

        try {
            $this->service->updateProfile($actor, $first->id, [
                'name' => 'Nome Invadido',
                'email' => 'invadido@example.com',
                'phone' => '11000000000',
            ]);
            $this->fail('Expected foreign profile update to be rejected.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::FORBIDDEN, $e->domainCode());
        }

        $this->assertDatabaseHas('clientes', [
            'id' => $first->id,
            'nome' => 'Ana Lima',
            'email' => 'ana@example.com',
            'phone' => '11999990000',
        ]);
    }

    public function test_admin_lists_all_renters_including_legacy_and_those_without_rentals(): void
    {
        $admin = User::factory()->create();
        $withAccount = $this->service->register($this->registration());
        $legacy = Cliente::factory()->create(['nome' => 'Cliente Legado']);

        $list = $this->service->listRenters($admin);

        $this->assertSame([$withAccount->id, $legacy->id], $list->pluck('id')->all());
        $this->assertNull($list->firstWhere('id', $legacy->id)->email);
    }

    public function test_admin_renter_list_is_empty_when_there_are_no_clientes(): void
    {
        $this->assertTrue($this->service->listRenters(User::factory()->create())->isEmpty());
    }

    public function test_admin_shows_renter_and_rejects_missing_id(): void
    {
        $admin = User::factory()->create();
        $renter = $this->service->register($this->registration());

        $shown = $this->service->showRenter($admin, $renter->id);

        $this->assertSame($renter->id, $shown->id);
        $this->assertSame('ana@example.com', $shown->email);

        try {
            $this->service->showRenter($admin, 999);
            $this->fail('Expected missing renter to be not_found.');
        } catch (BusinessRuleException $e) {
            $this->assertSame(BusinessRuleException::NOT_FOUND, $e->domainCode());
            $this->assertSame('Locatário não encontrado.', $e->getMessage());
        }
    }

    public function test_renter_is_forbidden_from_admin_renter_queries(): void
    {
        $renter = $this->service->register($this->registration());
        $actor = User::query()->findOrFail($renter->user_id);

        foreach (['list', 'show'] as $action) {
            try {
                if ($action === 'list') {
                    $this->service->listRenters($actor);
                } else {
                    $this->service->showRenter($actor, $renter->id);
                }
                $this->fail('Expected renter admin query to be forbidden.');
            } catch (BusinessRuleException $e) {
                $this->assertSame(BusinessRuleException::FORBIDDEN, $e->domainCode());
            }
        }
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array{name: string, email: string, phone: string, password: string}
     */
    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Lima',
            'email' => 'ana@example.com',
            'phone' => '11999990000',
            'password' => 'segredo12',
        ], $overrides);
    }
}
