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
      'size' => $this->screen_sizes[$this->_randomIndex($this->screen_sizes)],
      'width' => $this->screen_widths[$this->_randomIndex($this->screen_widths)],
      'height' => $this->screen_heights[$this->_randomIndex($this->screen_heights)],
      'touch' => $this->faker->boolean(20),
      'observation' => $this->faker->boolean ? $this->faker->text(50) : null,
    ];
  }

  private function _randomIndex(array $array): int
  {
    return mt_rand(0, (count($array) - 1));
  }
}
