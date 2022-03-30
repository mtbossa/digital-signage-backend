<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\User\Traits\UserTestsTrait;
use Tests\TestCase;

class UserTest extends TestCase
{
  use RefreshDatabase, UserTestsTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->user = $this->_createUser();
  }

  /** @test */
  public function user_should_be_able_to_login()
  {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/user')->assertOk();
  }

}
