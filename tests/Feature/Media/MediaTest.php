<?php

namespace Tests\Feature\Media;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class MediaTest extends TestCase
{
  use RefreshDatabase, MediaTestsTrait, WithFaker, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->defaultLocation = [
      'image' => 'intus/caxias/images',
      'video' => 'intus/caxias/videos'
    ];

    $this->media = $this->_createMedia();
  }

  /** @test */
  public function create_image_media_and_store_file_under_images_folder_and_ensure_it_can_be_downloaded()
  {
    Storage::fake('local');

    $description = 'Imagem de teste';

    $file = UploadedFile::fake()->image('image_test.jpg');
    $response = $this->postJson(route('medias.store'), ['description' => $description, 'file' => $file]);

    $response_data = $response->json();

    Storage::disk('local')->assertExists($this->defaultLocation['image'].'/'.$response_data['filename']);

    $this->assertDatabaseHas('medias', $response_data);

    $response->assertCreated()->assertJson($response_data);

    $this->getJson(route('media.download', $response_data['filename']))->assertDownload($response_data['filename']);
  }

  /** @test */
  public function create_video_media_and_store_file_under_videos_folder_and_ensure_it_can_be_downloaded()
  {
    Storage::fake('local');

    $description = 'Video de teste';

    $file = UploadedFile::fake()->create('image_test.mp4', 50000, 'video/mp4');
    $response = $this->postJson(route('medias.store'), ['description' => $description, 'file' => $file]);

    $response_data = $response->json();

    Storage::disk('local')->assertExists($this->defaultLocation['video'].'/'.$response_data['filename']);

    $this->assertDatabaseHas('medias', $response_data);

    $response->assertCreated()->assertJson($response_data);

    $this->getJson(route('media.download', $response_data['filename']))->assertDownload($response_data['filename']);
  }

  /** @test */
  public function update_media_description()
  {
    $update_values = ['description' => 'Alterando a descrição'];

    $response = $this->putJson(route('medias.update', $this->media->id), $update_values);

    $this->assertDatabaseHas('medias', $response->json());
    $response->assertJson($update_values)->assertOk();
  }

  /** @test */
  public function ensure_only_description_is_updated_even_if_more_fields_are_sent()
  {
    $current_values = $this->media->toArray();
    unset($current_values['description']);
    $update_values = $this->_makeMedia()->toArray();

    $this->putJson(route('medias.update', $this->media->id),
      $update_values)->assertJson(['description' => $update_values['description']])->assertOk();
    $this->assertDatabaseHas('medias', [...$current_values, 'description' => $update_values['description']]);
  }

  /** @test */
  public function delete_media()
  {
    $response = $this->deleteJson(route('medias.destroy', $this->media->id));
    $this->assertDatabaseMissing('medias', ['id' => $this->media->id]);
    $response->assertOk();
  }

  /** @test */
  public function fetch_single_media()
  {
    $this->getJson(route('medias.show',
      $this->media->id))->assertOk()->assertJson($this->media->toArray());
  }

  /** @test */
  public function fetch_all_medias()
  {
    $this->_createMedia();

    $this->getJson(route('medias.index'))->assertOk()->assertJsonCount(2)->assertJsonFragment($this->media->toArray());
  }
}
