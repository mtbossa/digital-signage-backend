<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayTest extends TestCase
{
  use RefreshDatabase;

  private Display $display;

  public function setUp(): void
  {
    parent::setUp();

    $this->display = $this->_createDisplay();
  }

  private function _createDisplay(array $data = null): Display
  {
    Display::factory()->create($data);
    return Display::first();
  }

  /** @test */
  public function create_display()
  {
    $display_data = $this->_makeDisplay()->toArray();

    $response = $this->postJson(route('displays.store'), $display_data);

    $this->assertDatabaseHas('displays', $display_data);

    $display = Display::find($response->json()['id']);
    $response->assertCreated()->assertJson($display->toArray());
  }

  private function _makeDisplay(array $data = null): Display
  {
    return Display::factory()->make($data);
  }

  /** @test */
  public function update_display()
  {
    $update_values = $this->_makeDisplay()->toArray();

    $response = $this->putJson(route('displays.update', $this->display->id), $update_values);

    $this->assertDatabaseHas('displays', $response->json());
    $response->assertJson($update_values)->assertOk();
  }

  /** @test */
  public function delete_display()
  {
    $response = $this->deleteJson(route('displays.destroy', $this->display->id));
    $this->assertDatabaseMissing('displays', ['id' => $this->display->id]);
    $response->assertOk();
  }

  /** @test */
  public function fetch_single_display()
  {
    $this->getJson(route('displays.show', $this->display->id))->assertOk()->assertJson($this->display->toArray());
  }

  /** @test */
  public function fetch_all_displays()
  {
    $this->_createDisplay();

    $this->getJson(route('displays.index'))->assertOk()->assertJsonCount(2)->assertJsonFragment($this->display->toArray());
  }
}
