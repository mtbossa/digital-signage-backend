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
  }

  /** @test */
  public function create_user_without_store_invitation()
  {
    $this->_authUser();
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
    $this->_authUser();
    $store = Store::factory()->create();
    $invitation_data = ['email' => $this->faker->email(), 'store_id' => $store->id];
    $response = $this->postJson(route('invitations.store'), $invitation_data)->assertCreated()->assertJson($invitation_data);
    $this->assertDatabaseHas('invitations', $response->json());
    Mail::assertQueued(UserInvitation::class);
  }

}
