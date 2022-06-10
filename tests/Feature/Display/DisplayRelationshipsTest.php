<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayRelationshipsTest extends TestCase
{
  use RefreshDatabase, DisplayTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function check_if_display_belongs_to_many_posts_relationship_is_working()
  {
    $media = Media::factory()->create();
    $display = Display::factory()->create();
    $posts_ids = Post::factory(2)->create(['media_id' => $media->id])->pluck('id')->toArray();

    $display->posts()->attach($posts_ids);

    $this->assertEquals(2, $display->posts()->count());
    $this->assertInstanceOf(Post::class, $display->posts[0]);

    foreach ($posts_ids as $post_id) {
      $this->assertDatabaseHas('display_post', ['post_id' => $post_id, 'display_id' => $display->id]);
    }
  }

  /** @test */
  public function a_display_may_have_a_raspberry()
  {
    $display = Display::factory()->create();
    Raspberry::factory()->create(['display_id' => $display->id]);

    $this->assertInstanceOf(Raspberry::class, $display->raspberry);
    $this->assertEquals(1, $display->raspberry->count());
    $this->assertDatabaseHas('raspberries', ['display_id' => $display->id]);
  }

  /** @test */
  public function create_display_and_relation_to_raspberry()
  {
    $raspberry = Raspberry::factory()->create();
    $display_data = $this->_makeDisplay()->toArray();

    $response = $this->postJson(route('displays.store', ['raspberry_id' => $raspberry->id]), $display_data);
    $new_display_id = $response->json()['id'];

    $this->assertDatabaseHas('displays', ['id' => $new_display_id]);
    $this->assertDatabaseHas('raspberries', ['display_id' => $new_display_id]);

    $display = Display::find($new_display_id);
    $response->assertCreated()->assertJson($display->toArray());
  }

  /** @test */
  public function update_displays_raspberry()
  {
    $this->display = Display::factory()->create();
    $rasp1 = Raspberry::factory()->create(['display_id' => $this->display->id]);
    $rasp2 = Raspberry::factory()->create();

    $response = $this->putJson(route('displays.update', $this->display->id),
      ['raspberry_id' => $rasp2->id]);

    $this->assertDatabaseHas('raspberries', ['id' => $rasp2->id, 'display_id' => $this->display->id]);
    $this->assertDatabaseHas('raspberries', ['id' => $rasp1->id, 'display_id' => null]);

    $response->assertOk();
  }

  /** @test */
  public function remove_displays_raspberry()
  {
    $this->display = $this->_createDisplay();
    Raspberry::factory()->create(['display_id' => $this->display->id]);

    $response = $this->putJson(route('displays.update', $this->display->id), ['raspberry_id' => null]);

    $this->assertDatabaseHas('raspberries', ['display_id' => null]);

    $response->assertOk();
  }
}
