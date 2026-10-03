<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Cliente;
use App\Models\User;
use App\Repositories\RenterRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RenterAccountService
{
    public function __construct(private readonly RenterRepository $renters)
    {
    }

    public function register(array $data): Cliente
    {
        $email = $this->normalizeEmail($data['email']);

        if ($this->renters->emailTaken($email)) {
            throw new BusinessRuleException(
                BusinessRuleException::EMAIL_TAKEN,
                'O e-mail já está em uso.',
            );
        }

        try {
            return DB::transaction(function () use ($data, $email) {
                $user = $this->renters->createUser([
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => Hash::make($data['password']),
                    'role' => 'renter',
                ]);

                return $this->renters->createCliente([
                    'nome' => $data['name'],
                    'user_id' => $user->id,
                    'email' => $email,
                    'phone' => $data['phone'],
                ]);
            });
        } catch (QueryException $e) {
            $this->rethrowUniqueEmail($e);
        }
    }

    public function profileFor(User $actor): Cliente
    {
        $renter = $this->renters->findByUserId($actor->id);

        if ($renter === null) {
            throw new BusinessRuleException(
                BusinessRuleException::NOT_FOUND,
                'Locatário não encontrado.',
            );
        }

        return $renter;
    }

    public function updateProfile(User $actor, int $renterId, array $data): Cliente
    {
        $renter = $this->renters->findById($renterId);

        if ($renter === null || $renter->user_id !== $actor->id) {
            throw new BusinessRuleException(
                BusinessRuleException::FORBIDDEN,
                'Acesso recusado.',
            );
        }

        if (array_key_exists('email', $data)) {
            $email = $this->normalizeEmail($data['email']);
            if ($this->renters->emailTaken($email, $actor->id, $renter->id)) {
                throw new BusinessRuleException(
                    BusinessRuleException::EMAIL_TAKEN,
                    'O e-mail já está em uso.',
                );
            }
            $data['email'] = $email;
        }

        try {
            return DB::transaction(function () use ($actor, $renter, $data) {
                $userAttributes = [];
                $clienteAttributes = [];

                if (array_key_exists('name', $data)) {
                    $userAttributes['name'] = $data['name'];
                    $clienteAttributes['nome'] = $data['name'];
                }

                if (array_key_exists('email', $data)) {
                    $userAttributes['email'] = $data['email'];
                    $clienteAttributes['email'] = $data['email'];
                }

                if (array_key_exists('phone', $data)) {
                    $clienteAttributes['phone'] = $data['phone'];
                }

                if ($userAttributes !== []) {
                    $this->renters->updateUser($actor, $userAttributes);
                }

                if ($clienteAttributes !== []) {
                    $this->renters->updateCliente($renter, $clienteAttributes);
                }

                return $renter->refresh();
            });
        } catch (QueryException $e) {
            $this->rethrowUniqueEmail($e);
        }
    }

    public function listRenters(User $actor): Collection
    {
        $this->assertAdmin($actor);

        return $this->renters->listAll();
    }

    public function showRenter(User $actor, int $renterId): Cliente
    {
        $this->assertAdmin($actor);

        $renter = $this->renters->findById($renterId);
        if ($renter === null) {
            throw new BusinessRuleException(
                BusinessRuleException::NOT_FOUND,
                'Locatário não encontrado.',
            );
        }

        return $renter;
    }

    private function assertAdmin(User $actor): void
    {
        if ($actor->role !== 'admin') {
            throw new BusinessRuleException(
                BusinessRuleException::FORBIDDEN,
                'Acesso recusado.',
            );
        }
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower($email);
    }

    private function rethrowUniqueEmail(QueryException $e): never
    {
        if ($e->getCode() === '23505') {
            throw new BusinessRuleException(
                BusinessRuleException::EMAIL_TAKEN,
                'O e-mail já está em uso.',
            );
        }

        throw $e;
    }
}
