<?php

namespace Tests\Traits;


use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait AuthUserTrait
{
  private User $user;

  private function _authUser()
  {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
  }


}
