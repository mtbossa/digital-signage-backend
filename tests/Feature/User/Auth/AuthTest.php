<?php

namespace Tests\Feature\User\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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
            'email' => $this->user->email, 'password' => 'password', // password is the default password for User factory
        ])->assertOk();
        $this->assertAuthenticated();
    }

    /** @test */
    public function ensure_sanctum_csrf_cookie_route_is_sending_correct_cookie()
    {
        $this->get('sanctum/csrf-cookie')->assertCookie('XSRF-TOKEN');
    }

    /** @test */
    public function check_if_user_route_is_returning_current_logged_user()
    {
        Sanctum::actingAs($this->user);

        $this->get('api/user')->assertJsonFragment(['id' => $this->user->id]);
    }

    /** @test */
    public function check_if_user_route_is_returning_correct_api_resource()
    {
        Sanctum::actingAs($this->user);

        $userResponse = [
            'data' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'is_admin' => $this->user->is_admin,
            ],
        ];

        $this->get('api/user')->assertExactJson($userResponse);
    }
}
