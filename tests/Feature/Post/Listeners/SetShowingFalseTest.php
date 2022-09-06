<?php


namespace Post\Listeners;

use App\Events\Post\PostMustStart;
use App\Events\Post\ShouldEndPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class SetShowingFalseTest extends TestCase
{
  use RefreshDatabase, PostTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->media = $this->_createMedia();
    $this->post = $this->_createPost([
      'media_id' => $this->media->id,
    ]);
  }

  /**
   * @test
   */
  public function must_set_post_showing_column_to_true()
  {
    Notification::fake();
    Bus::fake();
    Event::fakeExcept([PostMustStart::class]);

    event(new ShouldEndPost($this->post));

    $this->assertDatabaseHas('posts',
      ['id' => $this->post->id, 'showing' => false]);

  }
}
