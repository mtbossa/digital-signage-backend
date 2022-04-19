<?php

namespace Tests\Feature\User;

use App\Models\Invitation;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\Feature\User\Traits\UserTestsTrait;
use Tests\TestCase;

class UserTest extends TestCase
{
  use RefreshDatabase, UserTestsTrait, WithFaker, AuthUserTrait;

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

    $invitation = Invitation::factory()->withToken()->create([
      'inviter' => $this->user->id, 'is_admin' => $this->user->is_admin
    ]);

    $user_data = [
      'name' => $this->faker()->name, 'password' => 'A@oitudob3m', 'password_confirmation' => 'A@oitudob3m'
    ];

    $this->patchJson(route('invitations.update', $invitation->token),
      $user_data)->assertCreated()->assertJson(['name' => $user_data['name'], 'email' => $invitation->email]);

    $this->assertDatabaseHas('users',
      [
        'name' => $user_data['name'], 'email' => $invitation->email, 'store_id' => null,
        'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s')
      ]);
    $this->assertDatabaseHas('invitations', ['registered_at' => Carbon::now()->format('Y-m-d H:i:s')]);
  }

  /** @test */
  public function invited_user_with_store_should_be_able_to_accept_invitation_and_have_his_user_created()
  {
    $test_date = Carbon::now();
    Carbon::setTestNow($test_date);

    $store = Store::factory()->create();
    $invitation = Invitation::factory()->withToken()->create([
      'inviter' => $this->user->id, 'store_id' => $store->id, 'is_admin' => $this->user->is_admin
    ]);

    $user_data = [
      'name' => $this->faker()->name, 'password' => 'A@oitudob3m', 'password_confirmation' => 'A@oitudob3m'
    ];

    $this->patchJson(route('invitations.update', $invitation->token),
      $user_data)->assertCreated()->assertJson([
      'name' => $user_data['name'], 'email' => $invitation->email, 'store_id' => $store->id
    ]);

    $this->assertDatabaseHas('users',
      ['name' => $user_data['name'], 'email' => $invitation->email, 'store_id' => $store->id]);
    $this->assertDatabaseHas('invitations', ['registered_at' => Carbon::now()->format('Y-m-d H:i:s')]);
  }

  /** @test */
  public function invited_admin_user_with_store_should_be_able_to_accept_invitation_and_have_his_user_created()
  {
    $test_date = Carbon::now();
    Carbon::setTestNow($test_date);

    $store = Store::factory()->create();
    $invitation = Invitation::factory()->withToken()->create([
      'inviter' => $this->user->id, 'store_id' => $store->id, 'is_admin' => true
    ]);

    $user_data = [
      'name' => $this->faker()->name, 'password' => 'A@oitudob3m', 'password_confirmation' => 'A@oitudob3m'
    ];

    $this->patchJson(route('invitations.update', $invitation->token),
      $user_data)->assertCreated()->assertJson([
      'name' => $user_data['name'], 'email' => $invitation->email, 'store_id' => $store->id, 'is_admin' => true,
    ]);

    $this->assertDatabaseHas('users',
      ['name' => $user_data['name'], 'email' => $invitation->email, 'store_id' => $store->id, 'is_admin' => true]);
    $this->assertDatabaseHas('invitations', ['registered_at' => Carbon::now()->format('Y-m-d H:i:s')]);
  }

  /** @test */
  public function invited_admin_user_should_be_able_to_accept_invitation_and_have_his_user_created()
  {
    $test_date = Carbon::now();
    Carbon::setTestNow($test_date);

    $invitation = Invitation::factory()->withToken()->create(['inviter' => $this->user->id, 'is_admin' => true]);

    $user_data = [
      'name' => $this->faker()->name, 'password' => 'A@oitudob3m', 'password_confirmation' => 'A@oitudob3m'
    ];

    $this->patchJson(route('invitations.update', $invitation->token),
      $user_data)->assertCreated()->assertJson([
      'name' => $user_data['name'], 'email' => $invitation->email, 'is_admin' => true,
    ]);

    $this->assertDatabaseHas('users',
      ['name' => $user_data['name'], 'email' => $invitation->email, 'is_admin' => true]);
    $this->assertDatabaseHas('invitations', ['registered_at' => Carbon::now()->format('Y-m-d H:i:s')]);
  }

  /** @test */
  public function fetch_all_users()
  {
    $this->_authUser();

    $this->getJson(route('users.index'))->assertOk()->assertJsonCount(2)->assertJsonFragment($this->user->toArray());
  }
}
