<?php

namespace Tests\Feature\User\Traits;

use App\Models\User;

trait UserTestsTrait
{
    private User $user;

    private function _makeUser(array $data = null): User
    {
        return User::factory()->make($data);
    }

    private function _createUser(array $data = null): User
    {
        return User::factory()->create($data);
    }
}
