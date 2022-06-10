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
    $this->invitation = Invitation::factory()->withToken()->create(['inviter' => $this->user->id]);
  }

  /**
   * The route that creates the user is the invitations.update route
   *
   * @test
   * @dataProvider invalidUsers
   */
  public function cant_store_invalid_user($invalidData, $invalidFields)
  {
    $response = $this->patchJson(route('invitations.update', $this->invitation->token), $invalidData)
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('users', 1); // only the inviter
  }

  public function invalidUsers(): array
  {
    $user_data = ['name' => 'Mateus Bossa', 'password' => 'A1boitudo@me', 'password_confirmation' => 'A1boitudo@me'];
    return [
      'name as null' => [[...$user_data, 'name' => null], ['name']],
      'name as number' => [[...$user_data, 'name' => 1], ['name']],
      'name greater than 255' => [[...$user_data, 'name' => Str::random(256)], ['name']],
      'password as null' => [[...$user_data, 'password' => null], ['password']],
      'password as number' => [[...$user_data, 'password' => 12345678910], ['password']],
      'password lower then 8 char' => [[...$user_data, 'password' => 'A1boi', 'password_confirmation' => 'A1boi'], ['password']],
      'wrong password confirmation' => [[...$user_data, 'password_confirmation' => 'A1boitudaaa@me'], ['password']],
    ];
  }

}
