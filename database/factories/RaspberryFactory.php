<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory
 */
class RaspberryFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'mac_address' => Str::lower($this->faker->macAddress()),
      'serial_number' => $this->_generateSerialNumber(),
      'short_name' => $this->faker->sentence(1),
      'observation' => $this->faker->boolean ? $this->faker->text(50) : null,
    ];
  }

  private function _generateSerialNumber()
  {
    return "{$this->faker->randomNumber(9, true)}{$this->faker->randomNumber(4, true)}d";
  }

  public function booted()
  {
    return $this->state(function (array $attributes) {
      return ['last_boot' => $this->faker->dateTime()->format('Y-m-d H:m:s'),
      ];
    });
  }
}
