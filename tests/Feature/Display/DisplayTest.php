<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayTest extends TestCase
{
  use RefreshDatabase;

  private Display $display;

  /** @test */
  public function create_display()
  {
    $this->display = $this->_makeDisplay();
    $response = $this->postJson(route('displays.store'), $this->display->toArray());

    $this->assertDatabaseHas('displays', $this->display->toArray())->assertDatabaseCount('displays', 1);
    $response->assertCreated()->assertJson($this->display->toArray());
  }

  private function _makeDisplay(array $data = null): Display
  {
    return Display::factory()->make($data);
  }

  /** @test */
  public function observation_can_be_null()
  {
    $this->display = $this->_createDisplay(['observation' => null]);

    $this->assertDatabaseCount('displays', 1);
  }

  private function _createDisplay(array $data = null): Display
  {
    return Display::factory()->create($data);
  }
}
