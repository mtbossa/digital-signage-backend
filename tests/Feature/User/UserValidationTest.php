<?php

namespace Tests\Feature\User;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\Feature\User\Traits\UserTestsTrait;
use Tests\TestCase;

class UserValidationTest extends TestCase
{
  use RefreshDatabase, UserTestsTrait, AuthUserTrait, WithFaker;

  private Invitation $invitation;

  public function setUp(): void
  {
    parent::setUp();

    $this->user = User::factory()->create();
    $this->invitation = Invitation::factory()->unaccepted()->create(['inviter' => $this->user->id]);
  }

  /**
   * The route that creates the user is the invitations.update route
   *
   * @test
   * @dataProvider invalidUsers
   */
  public function cant_store_invalid_user($invalidData, $invalidFields)
  {
    $this->patchJson(route('invitations.update', $this->invitation->token), $invalidData)
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('users', 1); // only the inviter
  }

  public function invalidUsers(): array
  {
    $user_data = ['name' => 'Mateus Bossa', 'password' => 'A1boitudo@me'];
    return [
      'name as null' => [['name' => null], ['name']],
      'name as number' => [['name' => 1], ['name']],
      'name greater than 255' => [['name' => Str::random(256)], ['name']],
      'password as null' => [['password' => null], ['password']],
      'password as number' => [['password' => 1], ['password']],
      'password lower then 8 char' => [['password' => 'A1boi'], ['password']],
    ];
  }

}
