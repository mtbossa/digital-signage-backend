<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends Factory
 */
class MediaFactory extends Factory
{
  private array $available_types = ['image', 'video'];
  private array $available_extensions = [
    'image' => [
      'jpg', 'png', 'jpeg'
    ],
    'video' => [
      'mp4', 'avi'
    ]
  ];
  private string $default_path = 'intus/caxias';

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'description' => $this->faker->text(50),
      ...$this->generateImageOrVideo()
    ];
  }

  private function generateImageOrVideo(): array
  {
    $type = $this->faker->randomElement($this->available_types);
    $extension = $this->faker->randomElement($this->available_extensions[$type]);
    $filename = Str::of($this->faker->text(50))->replace('.', '')->snake()->toString().$extension;
    $hashed_filename = hash('md5', $filename);
    $type_folder = Str::plural($type);
    list($min, $max) = $type === 'image' ? [100, 30000] : [2000, 150000];

    return [
      'type' => $type,
      'extension' => $extension,
      'filename' => $filename,
      'path' => "{$this->default_path}/$type_folder/$hashed_filename",
      'size_kb' => $this->faker->numberBetween($min, $max),
    ];
  }
}
