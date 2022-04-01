<?php

namespace Tests\Feature\User\Invitation;

use App\Mail\UserInvitation;
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
  public function create_invitation()
  {
    $this->_authUser();
    Mail::fake();
    $email_data = ['email' => $this->faker->email()];
    $response = $this->postJson(route('invitations.store'), $email_data)->assertCreated()->assertJson($email_data);
    $this->assertDatabaseHas('invitations', $response->json());
    Mail::assertQueued(UserInvitation::class);
  }

}
