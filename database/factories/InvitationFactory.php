<?php

namespace Database\Factories;

use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class InvitationFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'email' => $this->faker->email,
    ];
  }

  public function withToken()
  {
    return $this->state(function (array $attributes) {
      return [
        'token' => Invitation::generateInvitationToken($attributes['email']),
      ];
    });
  }
}
