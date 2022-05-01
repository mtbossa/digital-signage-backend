<?php

namespace Tests\Feature\Post;

use App\Models\Display;
use App\Models\Post;
use App\Models\Recurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    $this->_authUser();
    $this->media = $this->_createMedia();
  }

  /**
   * @test
   */
  public function end_date_can_be_same_as_start_date()
  {
    $post_data = Post::factory()->make([
      'start_date' => '2022-01-01', 'end_date' => '2022-01-01', 'media_id' => $this->media->id
    ])->toArray();
    $this->postJson(route('posts.store'), [...$post_data, 'displays_ids' => null])
      ->assertCreated()->assertJson($post_data);

    $this->assertDatabaseCount('posts', 1);
  }

  /**
   * @test
   */
  public function start_date_and_end_date_must_not_be_passed_even_as_null_if_recurrence_id_is_present()
  {
    $recurrence = Recurrence::factory()->create();
    $post_data = Post::factory()->make([
      'start_date' => null, 'end_date' => null, 'media_id' => $this->media->id, 'recurrence_id' => $recurrence->id
    ])->toArray();
    $this->postJson(route('posts.store'), $post_data)
      ->assertUnprocessable()->assertJsonValidationErrors(['recurrence_id']);

    $this->assertDatabaseCount('posts', 0);
  }

  /**
   * @test
   */
  public function start_date_and_end_date_must_not_be_passed_if_recurrence_id_is_passed()
  {
    $recurrence = Recurrence::factory()->create();
    $post_data = Post::factory()->nonRecurrent()->make([
      'media_id' => $this->media->id, 'recurrence_id' => $recurrence->id
    ])->toArray();
    $this->postJson(route('posts.store'), $post_data)
      ->assertUnprocessable()->assertJsonValidationErrors(['recurrence_id']);

    $this->assertDatabaseCount('posts', 0);
  }

  /**
   * @test
   */
  public function start_date_and_end_date_are_required_if_recurrence_id_is_not_passed()
  {
    $recurrence = Recurrence::factory()->create();
    $post_data = Post::factory()->make([
      'start_date' => null, 'end_date' => null, 'media_id' => $this->media->id
    ])->toArray();
    $response = $this->postJson(route('posts.store'),
      $post_data)->assertUnprocessable()->assertJsonValidationErrors(['start_date', 'end_date']);

    $this->assertDatabaseCount('posts', 0);
  }

  /**
   * @test
   */
  public function displays_ids_must_be_array_when_creating_post()
  {
    Display::factory()->create();
    $post_data = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id])->toArray();
    $response = $this->postJson(route('posts.store'),
      [...$post_data, 'displays_ids' => 1])->assertUnprocessable()->assertJsonValidationErrorFor('displays_ids');

    $this->assertDatabaseCount('posts', 0);
  }

  /**
   * @test
   */
  public function all_displays_ids_must_exist_in_displays_table_when_creating_post()
  {
    $displays_ids = Display::factory(2)->create()->pluck('id')->toArray();
    $nonExistentId = ++Display::all()->last()->id;
    $post_data = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id])->toArray();
    $response = $this->postJson(route('posts.store'), [
      ...$post_data, 'displays_ids' => [...$displays_ids, $nonExistentId]
    ])->assertUnprocessable()->assertJsonValidationErrorFor('displays_ids');

    $this->assertDatabaseCount('posts', 0);
  }

  /**
   * @test
   */
  public function displays_ids_must_be_present_even_as_null_when_creating_post()
  {
    $displays_ids = Display::factory(2)->create()->pluck('id')->toArray();
    $nonExistentId = ++Display::all()->last()->id;
    $post_data = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id])->toArray();
    $response = $this->postJson(route('posts.store'),
      $post_data)->assertUnprocessable()->assertJsonValidationErrorFor('displays_ids');

    $this->assertDatabaseCount('posts', 0);
  }

  /**
   * @test
   */
  public function displays_ids_can_be_null_when_creating_post()
  {
    $displays_ids = Display::factory(2)->create()->pluck('id')->toArray();
    $nonExistentId = ++Display::all()->last()->id;
    $post_data = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id])->toArray();
    $response = $this->postJson(route('posts.store'), [...$post_data, 'displays_ids' => null])->assertCreated();

    $this->assertDatabaseCount('posts', 1);
  }

  /**
   * @test
   * @dataProvider invalidPosts
   */
  public function cant_store_invalid_post($invalidData, $invalidFields)
  {
    $this->postJson(route('posts.store'), $invalidData)
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('posts', 0);
  }

  public function invalidPosts(): array
  {
    $post_data = [
      'description' => 'Descrição de post', 'start_date' => '2022-01-01', 'end_date' => '2022-02-01',
      'start_time' => '08:30', 'end_time' => '10:00', 'expose_time' => 5000,
    ];
    return [
      'description greater than 100 char' => [[...$post_data, 'description' => Str::random(101)], ['description']],
      'description as null' => [[...$post_data, 'description' => null], ['description']],
      'description as empty string' => [[...$post_data, 'description' => ''], ['description']],
      'start_date with wrong format y/m/d' => [[...$post_data, 'start_date' => '2022/01/01'], ['start_date']],
      'end_date with wrong format y/m/d' => [[...$post_data, 'end_date' => '2022/01/01'], ['end_date']],
      'end_date less than start_date' => [
        [...$post_data, 'start_date' => '2022-01-02', 'end_date' => '2022-01-01'], ['end_date']
      ],
      'start_time with wrong format' => [[...$post_data, 'start_time' => '11:52pm'], ['start_time']],
      'end_time with wrong format' => [[...$post_data, 'end_time' => '11:52pm'], ['end_time']],
      'end_time less then start_time' => [
        [...$post_data, 'start_time' => '16:00', 'end_time' => '15:50'], ['end_time']
      ],
      'end_time same as start_time' => [
        [...$post_data, 'start_time' => '16:00', 'end_time' => '16:00'], ['end_time']
      ],
      'start_date empty with end_date' => [
        [...$post_data, 'start_date' => null, 'end_date' => '2022-01-01'], ['start_date']
      ],
      'end_date empty with start_date' => [
        [...$post_data, 'start_date' => '2022-01-01', 'end_date' => null], ['end_date']
      ],
      'media_id as null' => [
        [...$post_data, 'media_id' => null], ['media_id'],
      ],
      'media_id as string' => [
        [...$post_data, 'media_id' => ''], ['media_id'],
      ],
      'expose_time as null' => [
        [...$post_data, 'expose_time' => null], ['expose_time'],
      ],
      'expose_time as string' => [
        [...$post_data, 'expose_time' => ''], ['expose_time'],
      ],
      'expose_time less than 1000' => [
        [...$post_data, 'expose_time' => 999], ['expose_time'],
      ],
    ];
  }

}
