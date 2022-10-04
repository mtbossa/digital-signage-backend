<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DisplayFactory extends Factory
{
  private array $screen_sizes = [32, 27, 42, 38, 52, 50, 60, 62, 70, 72];
  private array $screen_widths = [1920, 720, 480];
  private array $screen_heights = [1080, 1280, 720];

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'name' => $this->faker->text(50),
      'size' => $this->faker->randomElement($this->screen_sizes),
      'width' => $this->faker->randomElement($this->screen_widths),
      'height' => $this->faker->randomElement($this->screen_heights),
      'touch' => $this->faker->boolean(20),
      'observation' => $this->faker->boolean ? $this->faker->text(50) : null,
      'store_id' => null,
    ];
  }
}
