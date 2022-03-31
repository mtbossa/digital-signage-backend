<?php

namespace Database\Factories;

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
      'token' => $this->faker->uuid(),
      'registered_at' => $this->faker->dateTimeBetween('-60 days', 'now'),
    ];
  }

  public function unregistered()
  {
    return $this->state(function (array $attributes) {
      return [
        'token' => null,
        'registered_at' => null,
      ];
    });
  }
}
