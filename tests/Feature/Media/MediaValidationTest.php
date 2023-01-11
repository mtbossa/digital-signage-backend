<?php

namespace Tests\Feature\Media;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class MediaValidationTest extends TestCase
{
    use RefreshDatabase, MediaTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
    }

    /**
     * @test
     *
     * @dataProvider invalidMedias
     */
    public function cant_store_invalid_media($invalidData, $invalidFields)
    {
        $this->postJson(route('medias.store'), $invalidData)
          ->assertJsonValidationErrors($invalidFields)
          ->assertUnprocessable();

        $this->assertDatabaseCount('medias', 0);
    }

    public function invalidMedias(): array
    {
        $media_data = [
            'description' => 'Descrição de mídia', 'file' => UploadedFile::fake()->create('image.png', 5000, 'image/png'),
        ];

        return [
            'description greater than 50 char' => [[...$media_data, 'description' => Str::random(51)], ['description']],
            'description as null' => [[...$media_data, 'description' => null], ['description']],
            'description as empty string' => [[...$media_data, 'description' => ''], ['description']],
            'file as null' => [[...$media_data, 'file' => null], ['file']],
            'file as number' => [
                [...$media_data, 'file' => 5], ['file'],
            ],
            'file as string' => [
                [...$media_data, 'file' => 'file_como_string'], ['file'],
            ],
            'file size greater than 150mb' => [
                [...$media_data, 'file' => UploadedFile::fake()->create('image.png', 150001, 'image/png')], ['file'],
            ],
            'file as pdf' => [
                [...$media_data, 'file' => UploadedFile::fake()->create('image.pdf')], ['file'],
            ],
            'file as bmp' => [
                [...$media_data, 'file' => UploadedFile::fake()->create('image.bmp')], ['file'],
            ],
            'file as mob' => [
                [...$media_data, 'file' => UploadedFile::fake()->create('image.mob')], ['file'],
            ],
        ];
    }
}
