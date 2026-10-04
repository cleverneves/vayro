<?php

namespace App\Repositories;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Collection;

class RenterRepository
{
    public function createUser(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function createCliente(array $attributes): Cliente
    {
        return Cliente::query()->create($attributes);
    }

    public function findById(int $id): ?Cliente
    {
        return Cliente::query()->find($id);
    }

    public function findByUserId(int $userId): ?Cliente
    {
        return Cliente::query()->where('user_id', $userId)->first();
    }

    public function listAll(): Collection
    {
        return Cliente::query()->orderBy('id')->get();
    }

    public function emailTaken(string $email, ?int $exceptUserId = null, ?int $exceptClienteId = null): bool
    {
        $users = User::query()->where('email', $email);
        if ($exceptUserId !== null) {
            $users->where('id', '!=', $exceptUserId);
        }

        if ($users->exists()) {
            return true;
        }

        $clientes = Cliente::query()->where('email', $email);
        if ($exceptClienteId !== null) {
            $clientes->where('id', '!=', $exceptClienteId);
        }

        return $clientes->exists();
    }

    public function updateUser(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user->refresh();
    }

    public function updateCliente(Cliente $cliente, array $attributes): Cliente
    {
        $cliente->update($attributes);

        return $cliente->refresh();
    }
}
