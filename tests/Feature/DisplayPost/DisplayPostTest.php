<?php

namespace Tests\Feature\DisplayPost;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
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

    $response = $this->getJson(route('displays.posts.index', ['display' => $this->display->id]))->assertOk();
    foreach ($posts as $key => $post) {
      $response->assertJsonFragment(['id' => $post->id]);
    }
  }

  /** @test */
  public function ensure_json_structure_is_clean_and_correct()
  {
    $media = Media::factory()->create();
    $posts = Post::factory(2)->create(['media_id' => $media->id]);

    $json_structure = [];
    foreach ($posts as $post) {
      $post->displays()->attach($this->display->id);

      $json_structure[] = [
        'id' => $post->id,
        'start_date' => $post->start_date,
        'end_date' => $post->end_date,
        'start_time' => $post->start_time,
        'end_time' => $post->end_time,
        'expose_time' => $post->expose_time,
        'media' => [
          'id' => $post->media->id, 'path' => $post->media->path, 'type' => $post->media->type,
          'filename' => $post->media->filename
        ]
      ];
    }
    $complete_json = ['data' => $json_structure];

    $response = $this->getJson(route('displays.posts.index',
      ['display' => $this->display->id]))->assertOk();
    $response->assertExactJson($complete_json);
  }

  /** @test */
  public function ensure_recurrent_posts_are_always_returned()
  {
    $now = Carbon::createFromDate(2022, 1, 2);
    Carbon::setTestNow($now);

    $recurrence = Recurrence::factory()->create();
    $media = Media::factory()->create();
    $recurrent_post = Post::factory()->create(['media_id' => $media->id, 'recurrence_id' => $recurrence->id]);
    $posts = Post::factory(2)->nonRecurrent()->create([
      'start_date' => '2021-01-01', 'end_date' => '2022-02-02', 'media_id' => $media->id
    ]);
    $posts[] = $recurrent_post;

    $recurrence_structure = [];
    foreach ($posts as $post) {
      $post->displays()->attach($this->display->id);

      if ($post->recurrence) {
        $recurrence_structure[] = [
          'id' => $post->id,
          'recurrence' => [
            'day' => $post->recurrence->day, 'isoweekday' => $post->recurrence->isoweekday,
            'month' => $post->recurrence->month, 'year' => $post->recurrence->year
          ]
        ];
      }
    }

    $complete_json = ['data' => $recurrence_structure];

    $res = $this->getJson(route('displays.posts.index',
      ['display' => $this->display->id]))->assertOk()->assertJson($complete_json);

    $this->getJson(route('displays.posts.index',
      ['display' => $this->display->id]), ['not_ended' => true])->assertOk()->assertJson($complete_json);
  }

  /** @test */
  public function ensure_only_posts_from_sent_display_id_are_returned()
  {
    $media = Media::factory()->create();
    $posts = Post::factory(2)->create(['media_id' => $media->id]);
    foreach ($posts as $post) {
      $post->displays()->attach($this->display->id);
    }

    $display_two = Display::factory()->create();
    $post_for_display_two = Post::factory()->create(['media_id' => $media->id]);
    $post_for_display_two->displays()->attach($display_two);

    $response = $this->getJson(route('displays.posts.index', ['display' => $this->display->id]))->assertOk();

    foreach ($posts as $key => $post) {
      $response->assertJsonFragment(['id' => $post->id]);
    }

    $response->assertJsonMissing(['id' => $post_for_display_two->id]);
  }

  /** @test */
  public function fetch_only_display_posts_which_end_date_is_greater_or_equal_today()
  {
    Carbon::setTestNow(Carbon::createFromDate(2022, 1, 2));
    $today = Carbon::now()->format('Y-m-d');

    $media = Media::factory()->create();

    $ended_post = Post::factory()->create(['end_date' => '2022-01-01', 'media_id' => $media->id]);
    $today_end_post = Post::factory()->create(['end_date' => '2022-01-02', 'media_id' => $media->id]);
    $tomorrow_end_post = Post::factory()->create(['end_date' => '2022-01-03', 'media_id' => $media->id]);

    $posts = [$ended_post, $today_end_post, $tomorrow_end_post];

    foreach ($posts as $post) {
      $post->displays()->attach($this->display->id);
    }

    $response = $this->getJson(route('displays.posts.index',
      ['display' => $this->display->id, 'not_ended' => true]))->assertOk();

    $response->assertJsonMissing(['end_date' => $ended_post->end_date]);
  }

}
