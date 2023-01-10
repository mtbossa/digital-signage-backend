<?php

namespace Database\Factories;

use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisplayFactory extends Factory
{
    public function configure(): DisplayFactory
    {
        return $this->afterCreating(function (Display $display) {
            if (is_null($display->pairing_code_id)) {
                return $display->plainTextToken
                    = $display->createToken('display_api_token')->plainTextToken;
            }
        });
    }
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
      'observation' => $this->faker->boolean ? $this->faker->text(50) : null,
      'store_id' => null,
        'pairing_code_id' => null,
    ];
  }
}
