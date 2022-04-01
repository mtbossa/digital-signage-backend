<?php

namespace Tests\Feature\Post;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    $this->post = $this->_createPost(['media_id' => $this->media->id]);
  }

  /** @test */
  public function create_post()
  {
    $post_data = $this->_makePost(['media_id' => $this->media->id])->toArray();

    $response = $this->postJson(route('posts.store'), $post_data);

    $this->assertDatabaseHas('posts', $post_data);

    $response->assertCreated()->assertJson($post_data);
  }

  /** @test */
  public function update_post()
  {
    $update_values = $this->_makePost()->toArray();

    $response = $this->putJson(route('posts.update', $this->post->id), $update_values);

    $this->assertDatabaseHas('posts', $response->json());
    $response->assertJson($update_values)->assertOk();
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
}
