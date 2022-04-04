<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class PostFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    $base_time = Carbon::create(null, null, null, 3, 0, 0);
    $start_time = (clone $base_time)->addHours($this->faker->numberBetween(0,
      8))->addMinutes($this->faker->numberBetween(0,
      59));
    $end_time = (clone $start_time)->addHours($this->faker->numberBetween(0,
      6))->addMinutes($this->faker->numberBetween('5',
      59));

    return [
      'description' => $this->faker->text(100),
      'start_time' => $start_time->format('H:i:s'),
      'end_time' => $end_time->format('H:i:s'),
      'expose_time' => $this->faker->numberBetween(1, 86400),
    ];
  }

  public function nonRecurrent()
  {
    $start_date = Carbon::instance($this->faker->dateTimeBetween('-1 months', '+1 months'));
    $end_date = (clone $start_date)->addDays($this->faker->numberBetween(0, 90));

    return $this->state(function (array $attributes) use ($start_date, $end_date) {
      return [
        'start_date' => $start_date->format('Y-m-d'),
        'end_date' => $end_date->format('Y-m-d'),
      ];
    });
  }
}
