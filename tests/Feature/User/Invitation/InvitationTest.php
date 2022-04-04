<?php

namespace Tests\Feature\User\Invitation;

use App\Mail\UserInvitation;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\Feature\User\Invitation\Traits\InvitationTestsTrait;
use Tests\TestCase;

class InvitationTest extends TestCase
{
  use RefreshDatabase, InvitationTestsTrait, AuthUserTrait, WithFaker;

  public function setUp(): void
  {
    parent::setUp();
    $this->_authUser();
    $this->invitation = $this->_createWithTokenInvitation(['inviter' => $this->user->id]);
  }

  /** @test */
  public function create_user_without_store_invitation()
  {
    Mail::fake();
    $email_data = ['email' => $this->faker->email()];
    $response = $this->postJson(route('invitations.store'), $email_data)->assertCreated()->assertJson($email_data);
    $this->assertDatabaseHas('invitations', $response->json());
    Mail::assertQueued(UserInvitation::class);
  }

  /** @test */
  public function create_user_with_store_invitation()
  {
    Mail::fake();
    $store = Store::factory()->create();
    $invitation_data = ['email' => $this->faker->email(), 'store_id' => $store->id];
    $response = $this->postJson(route('invitations.store'), $invitation_data)->assertCreated()->assertJson($invitation_data);
    $this->assertDatabaseHas('invitations', $response->json());
    Mail::assertQueued(UserInvitation::class);
  }

  /** @test */
  public function fetch_single_invitation()
  {
    $this->getJson(route('invitations.show',
      $this->invitation->id))->assertOk()->assertJson($this->invitation->toArray());
  }

  /** @test */
  public function fetch_all_invitations()
  {
    $this->_createWithTokenInvitation(['inviter' => $this->user->id]);

    $this->getJson(route('invitations.index'))->assertOk()->assertJsonCount(2)->assertJsonFragment($this->invitation->toArray());
  }

  /** @test */
  public function delete_invitation()
  {
    $response = $this->deleteJson(route('invitations.destroy', $this->invitation->id));
    $this->assertDatabaseMissing('invitations', ['id' => $this->invitation->id]);
    $response->assertOk();
  }

}
