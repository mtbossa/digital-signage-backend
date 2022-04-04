<?php

namespace Tests\Feature\Post;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PostTest extends TestCase
{
  use RefreshDatabase, PostTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->media = $this->_createMedia();
    $this->recurrence = $this->_createRecurrence();
    $this->post = $this->_createPost(['media_id' => $this->media->id, 'recurrence_id' => $this->recurrence->id]);
  }

  /** @test */
  public function create_post()
  {
    $this->withoutExceptionHandling();
    $post_data = $this->_makePost(['media_id' => $this->media->id], false)->toArray();

    $response = $this->postJson(route('posts.store'), $post_data);

    $this->assertDatabaseHas('posts', $post_data);

    $response->assertCreated()->assertJson($post_data);
  }
  
  /** @test */
  public function delete_post()
  {
    $response = $this->deleteJson(route('posts.destroy', $this->post->id));
    $this->assertDatabaseMissing('posts', ['id' => $this->post->id]);
    $response->assertOk();
  }

  /** @test */
  public function fetch_single_post()
  {
    $this->getJson(route('posts.show',
      $this->post->id))->assertOk()->assertJson($this->post->toArray());
  }

  /** @test */
  public function fetch_all_posts()
  {
    $this->_createPost(['media_id' => $this->media->id]);

    $this->getJson(route('posts.index'))->assertOk()->assertJsonCount(2)->assertJsonFragment($this->post->toArray());
  }

  /** @test */
  public function ensure_only_description_is_updated_even_if_more_fields_are_sent()
  {
    $old_media_id = $this->post->media->id;
    $new_values = ['description' => Str::random(20), 'media_id' => 2, 'recurrence_id' => 2];

    $this->putJson(route('posts.update', $this->post->id),
      $new_values)->assertOk()->assertJson(['description' => $new_values['description'], 'media_id' => $old_media_id]);
  }
}
