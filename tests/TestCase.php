<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function authHeaders(?User $user = null): array
    {
        $user ??= User::factory()->create();
        $user->wasRecentlyCreated = false;

        $token = auth('api')->login($user);

        return ['Authorization' => "Bearer {$token}"];
    }
}
