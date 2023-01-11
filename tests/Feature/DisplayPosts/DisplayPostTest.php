<?php

namespace Tests\Feature\DisplayPosts;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Raspberry;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayPostTest extends TestCase
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
        $this->media = Media::factory()->create();
    }

    /** @test */
    public function fetch_all_current_display_posts()
    {
        // Added a second display and raspberry because this route could
        // return the posts of the incorrect raspberry (the ones from $this->raspberry)
        // so need to make sure it's returning only the ones from the passed
        // so creates two to compare
        $secondDisplay = Display::factory()->create();
        $posts = Post::factory(2)->create(['media_id' => $this->media->id]);

        foreach ($posts as $post) {
            $post->displays()->attach($secondDisplay->id);
        }

        $response = $this->getJson(route('displays.posts.index',
            ['display' => $secondDisplay->id])
        )->assertOk();

        foreach ($posts as $key => $post) {
            $response->assertJsonFragment(['id' => $post->id]);
        }
    }

  /** @test */
  public function ensure_only_non_expired_posts_are_returned_by_default()
  {
      $now = Carbon::createFromFormat('Y-m-d H:i:s', '2022-01-01 15:00:00');
      $this->travelTo($now);

      $randomDisplay = Display::factory()->create();
      $randomDisplayPosts = Post::factory(2)->create([
          'start_date' => '2022-01-01', 'end_date' => '2022-01-03', 'media_id' => $this->media->id,
      ]);
      $randomDisplayExpiredPosts = Post::factory(2)->create([
          'start_date' => '2022-01-01', 'end_date' => '2022-01-01', 'start_time' => '14:00:00', 'end_time' => '14:59:00',
          'media_id' => $this->media->id,
      ]);
      foreach ([...$randomDisplayPosts, ...$randomDisplayExpiredPosts] as $randomDisplayPost) {
          $randomDisplayPost->displays()->attach($randomDisplay->id);
      }

      $correctDisplay = Display::factory()->create();
      $correctDisplayPosts = Post::factory(2)->create([
          'start_date' => '2022-01-01', 'end_date' => '2022-01-03', 'media_id' => $this->media->id, 'expired' => false,
      ]);
      $correctDisplayExpiredPosts = Post::factory(2)->create([
          'start_date' => '2022-01-01', 'end_date' => '2022-01-01', 'start_time' => '14:00:00', 'end_time' => '14:59:00',
          'expired' => true, 'media_id' => $this->media->id,
      ]);
      foreach ([...$correctDisplayPosts, ...$correctDisplayExpiredPosts] as $correctDisplayPost) {
          $correctDisplayPost->displays()->attach($correctDisplay->id);
      }

      $response = $this->getJson(route('displays.posts.index',
          ['display' => $correctDisplay->id])
      )->assertOk();

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
      $recurrence = Recurrence::factory()->create();
      $posts = Post::factory(2)->create([
          'media_id' => $this->media->id,
          'recurrence_id' => $recurrence->id,
          'expired' => false,
      ]);
      $nonRecurrentPost = Post::factory()->nonRecurrent()
        ->create(['media_id' => $this->media->id, 'expired' => false]);
      $posts = [...$posts, $nonRecurrentPost];

      $json_structure = [];
      foreach ($posts as $key => $post) {
          $post->displays()->attach($this->display->id);

          $structure = [
              'id' => $post->id,
              'start_date' => $post->start_date,
              'end_date' => $post->end_date,
              'start_time' => $post->start_time,
              'end_time' => $post->end_time,
              'expose_time' => $post->expose_time,
              'expired' => $post->expired,
              'media' => [
                  'id' => $post->media->id,
                  'path' => $post->media->path,
                  'type' => $post->media->type,
                  'filename' => $post->media->filename,
              ],
          ];

          if ($post->recurrence) {
              $structure['recurrence'] = [
                  'isoweekday' => $post->recurrence->isoweekday,
                  'day' => $post->recurrence->day,
                  'month' => $post->recurrence->month,
              ];
          }

          $json_structure[$key] = $structure;
      }
      $complete_json = ['data' => $json_structure];

      $response = $this->getJson(route('displays.posts.index',
          ['display' => $this->display->id]))->assertOk();
      $response->assertExactJson($complete_json);
  }

    /** @test */
    public function ensure_only_posts_from_sent_display_id_are_returned()
    {
        $posts = Post::factory(2)->create(['media_id' => $this->media->id]);

        foreach ($posts as $post) {
            $post->displays()->attach($this->display->id);
        }

        $display_two = Display::factory()->create();
        $post_for_display_two = Post::factory()
            ->create(['media_id' => $this->media->id]);
        $post_for_display_two->displays()->attach($display_two);

        $response = $this->getJson(route('displays.posts.index',
            ['display' => $this->display->id]))->assertOk();

        foreach ($posts as $key => $post) {
            $response->assertJsonFragment(['id' => $post->id]);
        }

        $response->assertJsonMissing(['id' => $post_for_display_two->id]);
    }

  /** @test */
  public function fetch_only_current_display_expired_posts()
  {
      $now = Carbon::createFromFormat('Y-m-d H:i:s', '2022-01-01 15:00:00');
      $this->travelTo($now);

      $randomDisplay = Display::factory()->create();
      $randomDisplayPosts = Post::factory(2)->create([
          'start_date' => '2022-01-01', 'end_date' => '2022-01-03', 'media_id' => $this->media->id,
      ]);
      $randomDisplayExpiredPosts = Post::factory(2)->create([
          'start_date' => '2022-01-01', 'end_date' => '2022-01-01', 'start_time' => '14:00:00', 'end_time' => '14:59:00',
          'media_id' => $this->media->id,
      ]);
      foreach ([...$randomDisplayPosts, ...$randomDisplayExpiredPosts] as $randomDisplayPost) {
          $randomDisplayPost->displays()->attach($randomDisplay->id);
      }

      $correctDisplay = Display::factory()->create();
      $correctDisplayPosts = Post::factory(2)->create([
          'start_date' => '2022-01-01', 'end_date' => '2022-01-03', 'media_id' => $this->media->id,
      ]);
      $correctDisplayExpiredPosts = Post::factory(2)->create([
          'start_date' => '2022-01-01', 'end_date' => '2022-01-01', 'start_time' => '14:00:00', 'end_time' => '14:59:00',
          'expired' => true, 'media_id' => $this->media->id,
      ]);
      foreach ([...$correctDisplayPosts, ...$correctDisplayExpiredPosts] as $correctDisplayPost) {
          $correctDisplayPost->displays()->attach($correctDisplay->id);
      }

      $response = $this->getJson(route('displays.posts.index',
          ['display' => $correctDisplay->id, 'expired' => true])
      )->assertOk();

      foreach ($correctDisplayExpiredPosts as $key => $post) {
          $response->assertJsonFragment(['id' => $post->id]);
      }
      foreach ($correctDisplayPosts as $key => $post) {
          $response->assertJsonMissing(['id' => $post->id]);
      }
      foreach ($randomDisplayExpiredPosts as $key => $post) {
          $response->assertJsonMissing(['id' => $post->id]);
      }

      $response->assertJsonCount(count($correctDisplayExpiredPosts), 'data');
  }

  /** @test */
  public function when_from_app_and_expired_true_should_return_correct_structure()
  {
      $posts = Post::factory(2)->create(['media_id' => $this->media->id, 'expired' => true]);
      foreach ($posts as $key => $post) {
          $post->displays()->attach($this->display->id);

          $structure = [
              'post_id' => $post->id,
              'media_id' => $post->media->id,
              'canDeleteMedia' => true,
          ];
          $json_structure[$key] = $structure;
      }
      $complete_json = ['data' => $json_structure];

      $response = $this->getJson(route('displays.posts.index',
          ['display' => $this->display->id, 'expired' => true, 'fromApp' => true]))->assertOk();
      $response->assertExactJson($complete_json);
  }

  /** @test */
  public function when_not_expired_post_depends_on_expired_post_media_should_send_can_delete_media_as_false()
  {
      $noNeededMedia = Media::factory()->create();
      $expiredPostsMediaNeeded = Post::factory(2)->create(['media_id' => $this->media->id, 'expired' => true]);
      $expiredPostNoMediaNeeded = Post::factory()->create(['media_id' => $noNeededMedia->id, 'expired' => true]);
      $nonExpiredPost = Post::factory()->create(['media_id' => $this->media->id, 'expired' => false]);

      foreach ([...$expiredPostsMediaNeeded, $nonExpiredPost, $expiredPostNoMediaNeeded] as $key => $post) {
          $post->displays()->attach($this->display->id);
      }

      foreach ([...$expiredPostsMediaNeeded, $expiredPostNoMediaNeeded] as $key => $post) {
          $structure = [
              'post_id' => $post->id,
              'media_id' => $post->media->id,
              'canDeleteMedia' => false,
          ];
          if ($post->id === $expiredPostNoMediaNeeded->id) {
              $structure['canDeleteMedia'] = true;
          }
          $json_structure[$key] = $structure;
      }
      $complete_json = ['data' => $json_structure];

      $response = $this->getJson(route('displays.posts.index',
          ['display' => $this->display->id, 'expired' => true, 'fromApp' => true]))->assertOk();
      $response->assertJson($complete_json);
  }
}
