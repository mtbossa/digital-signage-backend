<?php

namespace Media;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class MediaOptionTest extends TestCase
{
  use RefreshDatabase, MediaTestsTrait, WithFaker, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function ensure_post_media_options_is_returning_correct_amount()
  {
    $amount = 10;
    Media::factory($amount)->create();

    $this->getJson(route('medias.options'))->assertOk()->assertJsonCount($amount);
  }

  /** @test */
  public function ensure_post_media_options_structure_is_correct()
  {
    $medias = Media::factory(2)->create();

    $correctStructure = $medias->map(function (Media $media) {
      return ['id' => $media->id, 'description' => $media->description, 'path' => $media->path, 'type' => $media->type];
    });

    $this->getJson(route('medias.options'))->assertExactJson($correctStructure->toArray());
  }
}
