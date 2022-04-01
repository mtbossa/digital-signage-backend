<?php

namespace Tests\Feature\Media;

use App\Models\Media;
use App\Models\Post;
use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class MediaRelationshipsTest extends TestCase
{
  use RefreshDatabase, MediaTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function a_media_may_have_many_posts()
  {
    $display_amount = 3;
    $media = Media::factory()->create();
    $posts = Post::factory($display_amount)->create(['media_id' => $media->id]);

    $this->assertEquals($display_amount, $media->posts->count());
    $this->assertInstanceOf(Post::class, $media->posts[0]);
    $this->assertDatabaseHas('posts', ['id' => $posts[0]->id, 'media_id' => $media->id]);
  }
}
