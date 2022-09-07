<?php


namespace Post\Jobs;

use App\Jobs\ExpirePost;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class ExpirePostTest extends TestCase
{
  use RefreshDatabase, PostTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->media = $this->_createMedia();
  }

  /**
   * @test
   */
  public function should_set_post_expired_true()
  {
    $this->travelTo(Carbon::createFromFormat('Y-m-d H:i:s', '2022-01-01 15:00:00'));
    $dates = [
      'start_date' => '2022-01-01',
      'end_date' => '2022-01-02',
      'start_time' => '14:00:00',
      'end_time' => '16:00:00'
    ];
    $post = Post::factory()->create([...$dates, 'media_id' => $this->media->id]);

    $end = Carbon::createFromFormat('Y-m-d H:i:s', "{$dates['end_date']} {$dates['end_time']}");
    $delay = $end->diffInSeconds(now());

    ExpirePost::dispatch($post)->delay($delay);
    $this->travelTo(Carbon::createFromFormat('Y-m-d H:i:s', '2022-01-02 16:01:00'));

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'expired' => true]);
  }
}
