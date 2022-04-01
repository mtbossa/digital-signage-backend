<?php

namespace Tests\Feature\User\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\User\Traits\UserTestsTrait;
use Tests\TestCase;

class AuthTest extends TestCase
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
    $this->postJson('/login', [
      'email' => $this->user->email, 'password' => 'password' // password is the default password for User factory
    ])->assertOk();
    $this->assertAuthenticated();
  }

  /** @test */
  public function ensure_sanctum_csrf_cookie_route_is_sending_correct_cookie()
  {
    $this->get('sanctum/csrf-cookie')->assertCookie('XSRF-TOKEN');
  }
}
