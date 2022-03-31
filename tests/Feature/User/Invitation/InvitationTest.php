<?php

namespace Tests\Feature\User\Invitation;

use App\Mail\UserInvitation;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\User\Invitation\Traits\InvitationTestsTrait;
use Tests\TestCase;
use Tests\Traits\AuthUserTrait;

class InvitationTest extends TestCase
{
  use RefreshDatabase, InvitationTestsTrait, AuthUserTrait, WithFaker;

  public function setUp(): void
  {
    parent::setUp();
  }

  /** @test */
  public function create_invitation()
  {
    $this->_authUser();
    Mail::fake();
    $email_data = ['email' => $this->faker->email()];
    $response = $this->postJson(route('invitations.store'), $email_data)->assertCreated()->assertJson($email_data);
    $this->assertDatabaseHas('invitations', $response->json());
    Mail::assertQueued(UserInvitation::class);
  }

  /** @test */
  public function invited_user_should_be_able_to_accept_invitation_and_have_his_user_created()
  {
    $inviter = User::factory()->create();
    $invitation = Invitation::factory()->unaccepted()->create(['inviter' => $inviter]);

    $user_data = ['name' => $this->faker()->name, 'password' => 'A@oitudob3m', 'password_confirmation' => 'A@oitudob3m'];

    $this->patchJson(route('invitations.update', $invitation->token), $user_data)->assertCreated()->assertJson(['name' => $user_data['name'], 'email' => $invitation->email]);

    $this->assertDatabaseHas('users', ['name' => $user_data['name'], 'email' => $invitation->email]);
    $this->assertDatabaseHas('invitations', ['registered_at' => Carbon::now()->format('Y-m-d H:i:s')]);
  }

}
