<?php

namespace Tests\Feature\Recurrence;

use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Recurrence\Traits\RecurrenceTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RecurrenceRelationshipsTest extends TestCase
{
  use RefreshDatabase, RecurrenceTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function a_recurrence_may_have_many_posts()
  {
    $post_amount = 3;
    $recurrence = Recurrence::factory()->create();
    $media = Media::factory()->create();
    Post::factory()->create(['media_id' => $media->id]);
    $posts = Post::factory($post_amount)->create(['recurrence_id' => $recurrence->id, 'media_id' => $media->id]);

    $this->assertInstanceOf(Post::class, $recurrence->posts[0]);
    $this->assertEquals($post_amount, $recurrence->posts->count());
    $this->assertDatabaseHas('posts', ['id' => $posts[0]->id, 'recurrence_id' => $recurrence->id]);
  }
}
