<?php

namespace Tests\Feature\User\Invitation;

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

    $this->_authUser();
  }

  /** @test */
  public function create_invitation()
  {
    Mail::fake();
    $email_data = ['email' => $this->faker->email()];
    $response = $this->postJson(route('invitations.store'), $email_data)->assertCreated()->assertJson($email_data);
    $this->assertDatabaseHas('invitations', $response->json());
  }

}
