<?php

namespace Tests\Feature\User\Invitation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\User\Invitation\Traits\InvitationTestsTrait;
use Tests\TestCase;
use Tests\Traits\AuthUserTrait;

class InvitationValidationTest extends TestCase
{
  use RefreshDatabase, InvitationTestsTrait, AuthUserTrait, WithFaker;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function email_must_be_unique()
  {
    $this->invitation = $this->_createUnacceptedInvitation(['inviter' => $this->user->id]);

    $this->postJson(route('invitations.store'), ['email' => $this->invitation->email])
      ->assertJsonValidationErrorFor('email')
      ->assertUnprocessable();

    $this->assertDatabaseCount('invitations', 1); // only the one create for tests
  }

  /**
   * @test
   * @dataProvider invalidInvitations
   */
  public function cant_store_invalid_invitation($invalidData, $invalidFields)
  {
    $this->postJson(route('invitations.store'), $invalidData)
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('invitations', 0); // only the one create for tests
  }

  public function invalidInvitations(): array
  {
    return [
      'email can\'t be null' => [['email' => null], ['email']],
      'email as number' => [['email' => 1], ['email']],
    ];
  }

}
