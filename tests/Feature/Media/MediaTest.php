<?php

namespace Tests\Feature\Media;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\TestCase;

class MediaTest extends TestCase
{
  use RefreshDatabase, MediaTestsTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->media = $this->_createMedia();
  }

  /** @test */
  public function create_media()
  {
    $media_data = $this->_makeMedia()->toArray();

    $response = $this->postJson(route('medias.store'), $media_data);

    $this->assertDatabaseHas('medias', $media_data);

    $response->assertCreated()->assertJson($media_data);
  }

  /** @test */
  public function update_media()
  {
    $update_values = $this->_makeMedia()->toArray();

    $response = $this->putJson(route('medias.update', $this->media->id), $update_values);

    $this->assertDatabaseHas('medias', $response->json());
    $response->assertJson($update_values)->assertOk();
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
