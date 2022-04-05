<?php

namespace Tests\Feature\DisplayPost;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayPostTest extends TestCase
{
  use RefreshDatabase, DisplayTestsTrait, PostTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->display = $this->_createDisplay();
  }

  /** @test */
  public function fetch_all_display_posts()
  {
    $media = Media::factory()->create();
    $posts = Post::factory(2)->create(['media_id' => $media->id]);

    foreach ($posts as $post) {
      $post->displays()->attach($this->display->id);
    }

    $response = $this->getJson(route('displays.posts.index', $this->display->id))->assertOk();
    foreach ($posts as $key => $post) {
      $response->assertJsonPath("posts.{$key}.id", $post->id);
    }
  }


}
