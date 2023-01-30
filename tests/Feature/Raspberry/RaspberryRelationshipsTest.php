<?php

namespace Tests\Feature\Raspberry;

use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RaspberryRelationshipsTest extends TestCase
{
  use RefreshDatabase, RaspberryTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function a_raspberry_may_belong_to_a_display()
  {
    $display = Display::factory()->create();
    $raspberry = Raspberry::factory()->create(['display_id' => $display->id]);

    $this->assertInstanceOf(Display::class, $raspberry->display);
    $this->assertEquals(1, $raspberry->display->count());
    $this->assertDatabaseHas('raspberries', ['id' => $raspberry->id, 'display_id' => $display->id]);
  }

  /** @test */
  public function should_set_display_id_to_null_when_display_is_deleted()
  {
    $display = Display::factory()->create();
    $raspberry = Raspberry::factory()->create(['display_id' => $display->id]);

    $display->delete();
    $this->assertModelMissing($display);
    $this->assertModelExists($raspberry);
    $this->assertDatabaseHas('raspberries', ['id' => $raspberry->id, ['display_id' => null]]);
  }

  /** @test */
  public function create_raspberry_with_display()
  {
    $display = Display::factory()->create();
    $raspberry_data = $this->_makeRaspberry(['display_id' => $display->id])->toArray();

    $response = $this->postJson(route('raspberries.store'), $raspberry_data);

    $this->assertDatabaseHas('raspberries', ['id' => $response['id'], 'display_id' => $display->id]);

    $response->assertCreated()->assertJson($raspberry_data)->assertJsonFragment(['display_id' => $display->id]);
  }

  /** @test */
  public function update_raspberrys_display()
  {
    Display::factory(2)->create();
    $this->raspberry = $this->_createRaspberry(['display_id' => Display::first()->id]);

    $last_display = Display::all()->last();

    $response = $this->putJson(route('raspberries.update', $this->raspberry->id),
      [...$this->raspberry->toArray(), 'display_id' => $last_display->id]);

    $this->assertDatabaseHas('raspberries', ['id' => $this->raspberry->id, 'display_id' => $last_display->id]);

    $response->assertJsonFragment(['display_id' => $last_display->id])->assertOk();
  }

  /** @test */
  public function remove_raspberry_display()
  {
    Display::factory()->create();
    $this->raspberry = $this->_createRaspberry(['display_id' => Display::first()->id]);

    $response = $this->putJson(route('raspberries.update', $this->raspberry->id),
      [...$this->raspberry->toArray(), 'display_id' => null]);

    $this->assertDatabaseHas('raspberries', ['id' => $this->raspberry->id, 'display_id' => null]);

    $response->assertJsonFragment(['display_id' => null])->assertOk();
  }
}
