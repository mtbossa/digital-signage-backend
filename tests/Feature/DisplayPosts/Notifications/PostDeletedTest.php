<?php

namespace DisplayPosts\Notifications;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Raspberry;
use App\Notifications\DisplayPost\PostDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PostDeletedTest extends TestCase
{
  use RefreshDatabase, AuthUserTrait;

  private Display $display;
  private Raspberry $raspberry;
  private Media $media;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->display = Display::factory()->create();
    $this->raspberry = Raspberry::factory()->create(["display_id" => $this->display->id]);
    $this->media = Media::factory()->create();
  }

  /** @test */
  public function ensure_send_data_is_with_correct_structure()
  {
    $removedPost = Post::factory()->create(['media_id' => $this->media->id]);
    $correctStructure = [
      'post_id' => $removedPost->id,
      'media_id' => $this->media->id,
      'canDeleteMedia' => true,
    ];

    $notification = new PostDeleted($this->display, $removedPost->id, $removedPost->media->id);
    $sendData = $notification->toBroadcast($this->raspberry)->data;

    $this->assertEquals($sendData, $correctStructure);
  }

  /** @test */
  public function ensure_can_delete_media_is_false_when_other_post_on_same_display_depends_on_the_deleted_post_media()
  {
    $randomMedia = Media::factory()->create();
    $randomPost = Post::factory()->create(['media_id' => $randomMedia->id]);
    $randomPost->displays()->attach($this->display->id);

    $removedPost = Post::factory()->create(['media_id' => $this->media->id]);
    $keptPost = Post::factory()->create(['media_id' => $this->media->id]);
    $keptPost->displays()->attach($this->display->id);

    $removedPost->displays()->detach($this->display->id);

    $notification = new PostDeleted($this->display, $removedPost->id, $removedPost->media->id);
    $sendData = $notification->toBroadcast($this->raspberry)->data;

    $this->assertFalse($sendData['canDeleteMedia']);
  }

  /** @test */
  public function ensure_can_delete_media_is_true_when_no_other_post_on_display_depends_on_the_media()
  {
    $randomDisplay = Display::factory()->create();
    $randomMedia = Media::factory()->create();

    $randomPost = Post::factory()->create(['media_id' => $randomMedia->id]);
    $randomPost->displays()->attach($this->display->id);

    $keptPost = Post::factory()->create(['media_id' => $randomMedia->id]);
    $keptPost->displays()->attach($this->display->id);

    $removedPost = Post::factory()->create(['media_id' => $this->media->id]);
    $removedPost->displays()->attach($randomDisplay->id);

    $notification = new PostDeleted($this->display, $removedPost->id, $removedPost->media->id);
    $sendData = $notification->toBroadcast($this->raspberry)->data;

    $this->assertTrue($sendData['canDeleteMedia']);
  }
}
