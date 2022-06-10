<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class RecurrenceFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'description' => $this->faker->text(50),
      'isoweekday' => $this->faker->boolean(50) ? rand(1, 7) : null,
      'day' => $this->faker->boolean(80) ? rand(1, 31) : null,
      'month' => $this->faker->boolean(50) ? rand(1, 12) : null,
      'year' => $this->faker->boolean(50) ? rand(2021, 2025) : null,
    ];
  }
}
