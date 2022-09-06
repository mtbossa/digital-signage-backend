<?php


namespace Post\Jobs;

use App\Events\Post\PostMustEnd;
use App\Jobs\Post\EndPost;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class EndPostTest extends TestCase
{
    use RefreshDatabase, PostTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

      $this->_authUser();
      $this->media = $this->_createMedia();
      $this->post = Post::factory()->nonRecurrent()
        ->create(['media_id' => $this->media->id]);
    }

  /**
   * @test
   */
  public function when_completed_must_fire_PostMustEnd_event()
  {
    Notification::fake();
    Event::fake();

    EndPost::dispatch($this->post);

    Event::assertDispatched(PostMustEnd::class, 1);
  }
}
