<?php

namespace Tests\Feature\User;

use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\User\Traits\UserTestsTrait;
use Tests\TestCase;

class UserTest extends TestCase
{
  use RefreshDatabase, UserTestsTrait, WithFaker;

  public function setUp(): void
  {
    parent::setUp();

    $this->user = $this->_createUser();
  }

  /** @test */
  public function invited_user_should_be_able_to_accept_invitation_and_have_his_user_created()
  {
    $test_date = Carbon::now();
    Carbon::setTestNow($test_date);

    $inviter = User::factory()->create();
    $invitation = Invitation::factory()->unaccepted()->create(['inviter' => $inviter]);

    $user_data = [
      'name' => $this->faker()->name, 'password' => 'A@oitudob3m', 'password_confirmation' => 'A@oitudob3m'
    ];

    $this->patchJson(route('invitations.update', $invitation->token),
      $user_data)->assertCreated()->assertJson(['name' => $user_data['name'], 'email' => $invitation->email]);

    $this->assertDatabaseHas('users', ['name' => $user_data['name'], 'email' => $invitation->email]);
    $this->assertDatabaseHas('invitations', ['registered_at' => Carbon::now()->format('Y-m-d H:i:s')]);
  }
}