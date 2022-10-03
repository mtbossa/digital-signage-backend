<?php

namespace Tests\Feature\Raspberry;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Raspberry;
use App\Models\Recurrence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RaspberryDisplayPostsTest extends TestCase
{
  use RefreshDatabase, AuthUserTrait;

  private Display $display;
  private Raspberry $raspberry;
  private Media $media;
  private string $raspberryToken;

  public function setUp(): void
  {
    parent::setUp();

    $this->display = Display::factory()->create();
    $this->media = Media::factory()->create();
    $this->raspberry = Raspberry::factory()->create();
    $this->raspberryToken = $this->raspberry->plainTextToken;
    unset($this->raspberry->plainTextToken);
  }

  /** @test */
  public function ensure_404_when_user_trying_to_access_route()
  {
    Sanctum::actingAs(User::factory()->create());
    $this->getJson(route('raspberry.display.posts'))->assertNotFound();
  }

  /** @test */
  public function ensure_404_when_raspberry_is_not_connected_to_any_display()
  {
    $this->getJson(route('raspberry.display.posts'),
      ["Authorization" => "Bearer $this->raspberryToken"])->assertNotFound();
  }

  /** @test */
  public function fetch_all_current_raspberry_display_posts()
  {
    // Added a second display and raspberry because this route could
    // return the posts of the incorrect raspberry (the ones from $this->raspberry)
    // so need to make sure it's returning only the ones from the passed
    // so creates two to compare
    $secondRaspberry = Raspberry::factory()->create();
    $token = $secondRaspberry->plainTextToken;
    unset($secondRaspberry->plainTextToken);
    $secondDisplay = Display::factory()->create();
    $secondRaspberry->display()->associate($secondDisplay)->save();
    $posts = Post::factory(2)->create(['media_id' => $this->media->id]);

    foreach ($posts as $post) {
      $post->displays()->attach($secondDisplay->id);
    }

    $response = $this->getJson(route('raspberry.display.posts'), ["Authorization" => "Bearer $token"])->assertOk();

    foreach ($posts as $key => $post) {
      $response->assertJsonFragment(['id' => $post->id]);
    }
  }

  /** @test */
  public function ensure_only_non_expired_posts_are_returned_by_default()
  {
    $now = Carbon::createFromFormat('Y-m-d H:i:s', '2022-01-01 15:00:00');
    $this->travelTo($now);

    $randomRaspberry = Raspberry::factory()->create();
    unset($randomRaspberry->plainTextToken);
    $randomDisplay = Display::factory()->create();
    $randomRaspberry->display()->associate($randomDisplay)->save();
    $randomDisplayPosts = Post::factory(2)->create([
      'start_date' => '2022-01-01', 'end_date' => '2022-01-03', 'media_id' => $this->media->id
    ]);
    $randomDisplayExpiredPosts = Post::factory(2)->create([
      'start_date' => '2022-01-01', 'end_date' => '2022-01-01', 'start_time' => '14:00:00', 'end_time' => '14:59:00',
      'media_id' => $this->media->id
    ]);
    foreach ([...$randomDisplayPosts, ...$randomDisplayExpiredPosts] as $randomDisplayPost) {
      $randomDisplayPost->displays()->attach($randomDisplay->id);
    }

    $correctRaspberry = Raspberry::factory()->create();
    $token = $correctRaspberry->plainTextToken;
    unset($correctRaspberry->plainTextToken);
    $correctDisplay = Display::factory()->create();
    $correctRaspberry->display()->associate($correctDisplay)->save();
    $correctDisplayPosts = Post::factory(2)->create([
      'start_date' => '2022-01-01', 'end_date' => '2022-01-03', 'media_id' => $this->media->id, 'expired' => false
    ]);
    $correctDisplayExpiredPosts = Post::factory(2)->create([
      'start_date' => '2022-01-01', 'end_date' => '2022-01-01', 'start_time' => '14:00:00', 'end_time' => '14:59:00',
      'expired' => true, 'media_id' => $this->media->id
    ]);
    foreach ([...$correctDisplayPosts, ...$correctDisplayExpiredPosts] as $correctDisplayPost) {
      $correctDisplayPost->displays()->attach($correctDisplay->id);
    }

    $response = $this->getJson(route('raspberry.display.posts'), ["Authorization" => "Bearer $token"])->assertOk();

    foreach ($correctDisplayExpiredPosts as $key => $post) {
      $response->assertJsonMissing(['id' => $post->id]);
    }
    foreach ($correctDisplayPosts as $key => $post) {
      $response->assertJsonFragment(['id' => $post->id]);
    }
    foreach ($randomDisplayExpiredPosts as $key => $post) {
      $response->assertJsonMissing(['id' => $post->id]);
    }

    $response->assertJsonCount(count($correctDisplayExpiredPosts), 'data');
  }

  /** @test */
  public function ensure_json_structure_is_clean_and_correct()
  {
    $this->raspberry->display()->associate($this->display)->save();
    $recurrence = Recurrence::factory()->create();
    $posts = Post::factory(2)->create([
      'media_id' => $this->media->id, 'recurrence_id' => $recurrence->id, 'expired' => false
    ]);
    $nonRecurrentPost = Post::factory()->nonRecurrent()->create(['media_id' => $this->media->id, 'expired' => false]);
    $posts = [...$posts, $nonRecurrentPost];

    $json_structure = [];
    foreach ($posts as $key => $post) {
      $post->displays()->attach($this->display->id);

      $structure = [
        'id' => $post->id, 'start_date' => $post->start_date, 'end_date' => $post->end_date,
        'start_time' => $post->start_time, 'end_time' => $post->end_time, 'expose_time' => $post->expose_time,
        'expired' => $post->expired, 'media' => [
          'id' => $post->media->id, 'path' => $post->media->path, 'type' => $post->media->type,
          'filename' => $post->media->filename
        ],
      ];

      if ($post->recurrence) {
        $structure['recurrence'] = [
          'isoweekday' => $post->recurrence->isoweekday, 'day' => $post->recurrence->day,
          'month' => $post->recurrence->month,
        ];
      }

      $json_structure[$key] = $structure;

    }
    $complete_json = ['data' => $json_structure];

    $response = $this->getJson(route('raspberry.display.posts'),
      ["Authorization" => "Bearer $this->raspberryToken"])->assertOk();
    $response->assertExactJson($complete_json);
  }

  /** @test */
  public function ensure_only_posts_from_auth_raspberry_are_returned()
  {
    $this->raspberry->display()->associate($this->display)->save();
    $new_media = Media::factory()->create(["id" => 99999]);
    $posts = Post::factory(2)->create(['media_id' => $new_media->id]);

    foreach ($posts as $post) {
      $post->displays()->attach($this->display->id);
    }

    $display_two = Display::factory()->create();
    $post_for_display_two = Post::factory()->create(['media_id' => $new_media->id]);
    $post_for_display_two->displays()->attach($display_two);

    $response = $this->getJson(route('raspberry.display.posts'),
      ["Authorization" => "Bearer $this->raspberryToken"])->assertOk();

    foreach ($posts as $key => $post) {
      $response->assertJsonFragment(['id' => $post->id]);
    }

    $response->assertJsonMissing(['id' => $post_for_display_two->id]);
  }
}
