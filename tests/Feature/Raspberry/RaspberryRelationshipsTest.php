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
  public function create_raspberry_with_display()
  {
    $display = Display::factory()->create();
    $raspberry_data = $this->_makeRaspberry()->toArray();

    $response = $this->postJson(route('raspberries.store', ['display_id' => $display->id]), $raspberry_data);

    $this->assertDatabaseHas('raspberries', ['display_id' => $display->id]);

    $raspberry = Raspberry::find($response->json()['id']);
    $response->assertCreated()->assertJson($raspberry->toArray())->assertJsonFragment(['display_id' => $display->id]);
  }

  /** @test */
  public function update_raspberrys_display()
  {
    Display::factory(2)->create();
    $this->raspberry = $this->_createRaspberry(['display_id' => Display::first()->id]);

    $last_display = Display::all()->last();

    $response = $this->putJson(route('raspberries.update', $this->raspberry->id),
      ['display_id' => $last_display->id]);

    $this->assertDatabaseHas('raspberries', ['display_id' => $last_display->id]);

    $response->assertJsonFragment(['display_id' => $last_display->id])->assertOk();
  }

  /** @test */
  public function remove_raspberry_display()
  {
    Display::factory()->create();
    $this->raspberry = $this->_createRaspberry(['display_id' => Display::first()->id]);

    $response = $this->putJson(route('raspberries.update', $this->raspberry->id), ['display_id' => null]);

    $this->assertDatabaseHas('raspberries', ['display_id' => null]);

    $response->assertJsonFragment(['display_id' => null])->assertOk();
  }
}
