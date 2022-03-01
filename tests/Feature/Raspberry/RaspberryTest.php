<?php

namespace Tests\Feature\Raspberry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\TestCase;

class RaspberryTest extends TestCase
{
  use RefreshDatabase, RaspberryTestsTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->raspberry = $this->_createRaspberry();
  }

  /** @test */
  public function create_raspberry()
  {
    $raspberry_data = $this->_makeRaspberry()->toArray();

    $response = $this->postJson(route('raspberries.store'), $raspberry_data);

    $this->assertDatabaseHas('raspberries', $raspberry_data);

    $response->assertCreated()->assertJson($raspberry_data);
  }

  /** @test */
  public function update_raspberry()
  {
    $update_values = $this->_makeRaspberry()->toArray();

    $response = $this->putJson(route('raspberries.update', $this->raspberry->id), $update_values);

    $this->assertDatabaseHas('raspberries', $response->json());
    $response->assertJson($update_values)->assertOk();
  }

  /** @test */
  public function delete_raspberry()
  {
    $response = $this->deleteJson(route('raspberries.destroy', $this->raspberry->id));
    $this->assertDatabaseMissing('raspberries', ['id' => $this->raspberry->id]);
    $response->assertOk();
  }

  /** @test */
  public function fetch_single_raspberry()
  {
    $this->getJson(route('raspberries.show',
      $this->raspberry->id))->assertOk()->assertJson($this->raspberry->toArray());
  }

  /** @test */
  public function fetch_all_displays()
  {
    $this->_createRaspberry();

    $this->getJson(route('raspberries.index'))->assertOk()->assertJsonCount(2)->assertJsonFragment($this->raspberry->toArray());
  }
}
