<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
  private $available_types = ['image', 'video'];
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
          'description' => $this->faker->text(50),
          'type' => $this->faker->randomElement($this->available_types), // image or video
          'path',
          'filename',
          'extension',
        ];
    }
}
