<?php

namespace Tests\Feature\Post;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PostValidationTest extends TestCase
{
    use RefreshDatabase, PostTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        Bus::fake();
        Event::fake();

        $this->_authUser();
        $this->media = $this->_createMedia();
    }

    /**
     * @test
     */
    public function end_date_can_be_same_as_start_date()
    {
      $now = Carbon::createFromFormat("Y-m-d", "2022-01-01");
      $this->travelTo($now);
      $videoMedia = Media::factory()->create(['type' => 'video']);
      $post_data = Post::factory()->make([
        'start_date' => '2022-01-01', 'end_date' => '2022-01-01',
        'media_id' => $videoMedia->id, 'expose_time' => null
      ])->toArray();
      $this->postJson(route('posts.store'),
        [...$post_data, 'displays_ids' => null])
        ->assertCreated()->assertJson($post_data);

      $this->assertDatabaseCount('posts', 1);
    }

    /**
     * @test
     */
    public function start_date_and_end_date_must_not_be_passed_even_as_null_if_recurrence_id_is_present(
    )
    {
        $recurrence = Recurrence::factory()->create();
        $post_data = Post::factory()->make([
            'start_date' => null, 'end_date' => null,
            'media_id'   => $this->media->id, 'recurrence_id' => $recurrence->id
        ])->toArray();
        $this->postJson(route('posts.store'), $post_data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['recurrence_id']);

        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * @test
     */
    public function start_date_and_end_date_must_not_be_passed_if_recurrence_id_is_passed(
    )
    {
        $recurrence = Recurrence::factory()->create();
        $post_data = Post::factory()->nonRecurrent()->make([
            'media_id' => $this->media->id, 'recurrence_id' => $recurrence->id
        ])->toArray();
        $this->postJson(route('posts.store'), $post_data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['recurrence_id']);

        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * @test
     */
    public function start_date_and_end_date_are_required_if_recurrence_id_is_not_passed(
    )
    {
        $recurrence = Recurrence::factory()->create();
        $post_data = Post::factory()->make([
            'start_date' => null, 'end_date' => null,
            'media_id'   => $this->media->id
        ])->toArray();
        $response = $this->postJson(route('posts.store'),
            $post_data)->assertUnprocessable()
            ->assertJsonValidationErrors(['start_date', 'end_date']);

        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * @test
     */
    public function displays_ids_must_be_array_when_creating_post()
    {
        Display::factory()->create();
        $post_data = Post::factory()->nonRecurrent()
            ->make(['media_id' => $this->media->id])->toArray();
        $response = $this->postJson(route('posts.store'),
            [...$post_data, 'displays_ids' => 1])->assertUnprocessable()
            ->assertJsonValidationErrorFor('displays_ids');

        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * @test
     */
    public function all_displays_ids_must_exist_in_displays_table_when_creating_post(
    )
    {
        $displays_ids = Display::factory(2)->create()->pluck('id')->toArray();
        $nonExistentId = ++Display::all()->last()->id;
        $post_data = Post::factory()->nonRecurrent()
            ->make(['media_id' => $this->media->id])->toArray();
        $response = $this->postJson(route('posts.store'), [
            ...$post_data, 'displays_ids' => [...$displays_ids, $nonExistentId]
        ])->assertUnprocessable()->assertJsonValidationErrorFor('displays_ids');

        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * @test
     */
    public function displays_ids_must_be_present_even_as_null_when_creating_post(
    )
    {
        $displays_ids = Display::factory(2)->create()->pluck('id')->toArray();
        $nonExistentId = ++Display::all()->last()->id;
        $post_data = Post::factory()->nonRecurrent()
            ->make(['media_id' => $this->media->id])->toArray();
        $response = $this->postJson(route('posts.store'),
            $post_data)->assertUnprocessable()
            ->assertJsonValidationErrorFor('displays_ids');

        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * @test
     */
    public function displays_ids_can_be_null_when_creating_post()
    {
      $displays_ids = Display::factory(2)->create()->pluck('id')->toArray();
      $nonExistentId = ++Display::all()->last()->id;
      $post_data = Post::factory()->nonRecurrent()
        ->make(['media_id' => $this->media->id])->toArray();
      $response = $this->postJson(route('posts.store'),
        [...$post_data, 'displays_ids' => null])->assertCreated();

      $this->assertDatabaseCount('posts', 1);
    }

  /**
   * @test
   */
  public function when_media_is_image_expose_time_is_required()
  {
    $imageMedia = Media::factory()->create(['type' => 'image']);
    $postData = Post::factory()->nonRecurrent()->make(['media_id' => $imageMedia->id, 'displays_ids' => []])->toArray();
    $response = $this->postJson(route('posts.store'),
      [...$postData, 'expose_time' => null])->assertUnprocessable()
      ->assertJsonValidationErrorFor('expose_time');
  }

  /**
   * @test
   */
  public function when_media_is_image_expose_time_must_be_greater_or_equal_to_1000_ms()
  {
    $imageMedia = Media::factory()->create(['type' => 'image']);
    $postData = Post::factory()->make(['media_id' => $imageMedia->id])->toArray();
    $response = $this->postJson(route('posts.store'),
      [...$postData, 'expose_time' => 999])->assertUnprocessable()
      ->assertJsonValidationErrorFor('expose_time');
  }

  /**
   * @test
   */
  public function when_media_is_image_expose_time_must_be_less_then_or_equal_to_3_600_000_ms()
  {
    $imageMedia = Media::factory()->create(['type' => 'image']);
    $postData = Post::factory()->make(['media_id' => $imageMedia->id])->toArray();
    $response = $this->postJson(route('posts.store'),
      [...$postData, 'expose_time' => 3600001])->assertUnprocessable()
      ->assertJsonValidationErrorFor('expose_time');
  }

  /**
   * @test
   */
  public function ensure_cant_create_post_with_start_date_before_today()
  {
    $today = "2022-01-01 10:00:00";
    $wrongStartDate = "2021-12-31";

    $now = Carbon::createFromFormat("Y-m-d H:i:s", $today);
    $this->travelTo($now);

    $postData = Post::factory()->make([
      'start_date' => $wrongStartDate, 'end_date' => $wrongStartDate, 'media_id' => $this->media->id,
      'displays_ids' => []
    ])->toArray();
    $response = $this->postJson(route('posts.store'), $postData)->assertUnprocessable()
      ->assertJsonValidationErrorFor('start_date');
  }

  /**
   * @test
   */
  public function ensure_can_create_post_with_start_date_same_as_today()
  {
    $today = "2022-01-01 10:00:00";
    $startDate = "2022-01-01";
    $startTime = "10:01:00";
    $endTime = "10:02:00";

    $now = Carbon::createFromFormat("Y-m-d H:i:s", $today);
    $this->travelTo($now);

    $postData = Post::factory()->make([
      'start_date' => $startDate, 'end_date' => $startDate, 'start_time' => $startTime, 'end_time' => $endTime,
      'media_id' => $this->media->id, 'displays_ids' => []
    ])->toArray();
    $response = $this->postJson(route('posts.store'), $postData)->assertCreated();
  }


  /**
   * @test
   */
  public function when_media_is_video_expose_time_must_null()
  {
    $videoMedia = Media::factory()->create(['type' => 'video']);
    $postData = Post::factory()->make(['media_id' => $videoMedia->id])->toArray();
    $response = $this->postJson(route('posts.store'),
      [...$postData, 'expose_time' => 1000])->assertUnprocessable()
      ->assertJsonValidationErrorFor('expose_time');

    $correctPostData = Post::factory()->nonRecurrent()->make([
      'media_id' => $videoMedia->id, 'expose_time' => null, 'displays_ids' => []
    ])->toArray();
    $this->postJson(route('posts.store'),
      $correctPostData)->assertCreated();
  }

  /**
   * @test
   * @dataProvider invalidPosts
   */
  public function cant_store_invalid_post($invalidData, $invalidFields)
  {
    $now = Carbon::createFromFormat("Y-m-d H:i:s", "2022-01-01 10:00:00");
    $this->travelTo($now);
    $response = $this->postJson(route('posts.store'), $invalidData);

    $response->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('posts', 0);
  }

  public function invalidPosts(): array
  {
    $post_data = [
      'description' => 'Descrição de post', 'start_date' => '2022-01-01',
      'end_date' => '2022-02-01',
      'start_time' => '08:30', 'end_time' => '10:00',
        ];
        return [
            'description greater than 100 char'  => [
                [
                    ...$post_data, 'description' => Str::random(101)
                ], ['description']
            ],
            'description as null'                => [
                [
                    ...$post_data, 'description' => null
                ], ['description']
            ],
            'description as empty string'        => [
                [
                    ...$post_data, 'description' => ''
                ], ['description']
            ],
            'start_date with wrong format y/m/d' => [
                [
                    ...$post_data, 'start_date' => '2022/01/01'
                ], ['start_date']
            ],
            'end_date with wrong format y/m/d'   => [
                [
                    ...$post_data, 'end_date' => '2022/01/01'
                ], ['end_date']
            ],
            'end_date less than start_date'      => [
                [
                    ...$post_data, 'start_date' => '2022-01-02',
                    'end_date'                  => '2022-01-01'
                ], ['end_date']
            ],
            'start_time with wrong format'       => [
                [
                    ...$post_data, 'start_time' => '11:52pm'
                ], ['start_time']
            ],
            'end_time with wrong format'         => [
                [
                    ...$post_data, 'end_time' => '11:52pm'
                ], ['end_time']
            ],
            'end_time less then start_time'      => [
                [...$post_data, 'start_time' => '16:00', 'end_time' => '15:50'],
                ['end_time']
            ],
            'end_time same as start_time'        => [
                [...$post_data, 'start_time' => '16:00', 'end_time' => '16:00'],
                ['end_time']
            ],
            'start_date empty with end_date'     => [
                [
                    ...$post_data, 'start_date' => null,
                    'end_date'                  => '2022-01-01'
                ], ['start_date']
            ],
            'end_date empty with start_date'     => [
                [
                    ...$post_data, 'start_date' => '2022-01-01',
                    'end_date'                  => null
                ], ['end_date']
            ],
            'media_id as null'                   => [
                [...$post_data, 'media_id' => null], ['media_id'],
            ],
            'media_id as string'                 => [
                [...$post_data, 'media_id' => ''], ['media_id'],
            ],
        ];
    }

}
