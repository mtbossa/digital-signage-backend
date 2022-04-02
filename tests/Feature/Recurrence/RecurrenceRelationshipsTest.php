<?php

namespace Tests\Feature\Recurrence;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Recurrence\Traits\RecurrenceTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RecurrenceRelationshipsTest extends TestCase
{
  use RefreshDatabase, RecurrenceTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function a_recurrence_may_have_many_posts()
  {
    $post_amount = 3;
    $recurrence = Recurrence::factory()->create();
    $media = Media::factory()->create();
    Post::factory()->create(['media_id' => $media->id]);
    $posts = Post::factory($post_amount)->create(['recurrence_id' => $recurrence->id, 'media_id' => $media->id]);

    $this->assertInstanceOf(Post::class, $recurrence->posts[0]);
    $this->assertEquals($post_amount, $recurrence->posts->count());
    $this->assertDatabaseHas('posts', ['id' => $posts[0]->id, 'recurrence_id' => $recurrence->id]);
  }

  /** @test */
  public function should_set_display_id_to_null_when_display_is_deleted()
  {
    $display = Display::factory()->create();
    $recurrence = Recurrence::factory()->create(['display_id' => $display->id]);

    $display->delete();
    $this->assertModelMissing($display);
    $this->assertModelExists($recurrence);
    $this->assertDatabaseHas('raspberries', ['id' => $recurrence->id, ['display_id' => null]]);
  }

  /** @test */
  public function create_recurrence_with_display()
  {
    $display = Display::factory()->create();
    $recurrence_data = $this->_makeRecurrence()->toArray();

    $response = $this->postJson(route('raspberries.store', ['display_id' => $display->id]), $recurrence_data);

    $this->assertDatabaseHas('raspberries', ['id' => $response['id'], 'display_id' => $display->id]);

    $recurrence = Recurrence::find($response->json()['id']);
    $response->assertCreated()->assertJson($recurrence->toArray())->assertJsonFragment(['display_id' => $display->id]);
  }

  /** @test */
  public function update_recurrences_display()
  {
    Display::factory(2)->create();
    $this->recurrence = $this->_createRecurrence(['display_id' => Display::first()->id]);

    $last_display = Display::all()->last();

    $response = $this->putJson(route('raspberries.update', $this->recurrence->id),
      ['display_id' => $last_display->id]);

    $this->assertDatabaseHas('raspberries', ['id' => $this->recurrence->id, 'display_id' => $last_display->id]);

    $response->assertJsonFragment(['display_id' => $last_display->id])->assertOk();
  }

  /** @test */
  public function remove_recurrence_display()
  {
    Display::factory()->create();
    $this->recurrence = $this->_createRecurrence(['display_id' => Display::first()->id]);

    $response = $this->putJson(route('raspberries.update', $this->recurrence->id), ['display_id' => null]);

    $this->assertDatabaseHas('raspberries', ['id' => $this->recurrence->id, 'display_id' => null]);

    $response->assertJsonFragment(['display_id' => null])->assertOk();
  }
}
