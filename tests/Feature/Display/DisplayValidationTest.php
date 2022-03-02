<?php

namespace Tests\Feature\Display;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\TestCase;

class DisplayValidationTest extends TestCase
{
  use RefreshDatabase, DisplayTestsTrait;

  /** @test */
  public function touch_if_false_by_default()
  {
    $display_data = $this->_makeDisplay(['touch' => null])->toArray();
    unset($display_data['touch']);

    $this->postJson(route('displays.store'), $display_data);

    $this->assertDatabaseCount('displays', 1);
    $this->assertDatabaseHas('displays', ['touch' => false]);
  }

  /** @test */
  public function touch_must_be_boolean()
  {
    $display_data = $this->_makeDisplay()->toArray();
    // Number
    $display_data['touch'] = 2;
    $response = $this->postJson(route('displays.store'), $display_data);
    $this->assertDatabaseCount('displays', 0);
    $response->assertUnprocessable()->assertJsonValidationErrorFor('touch');

    // String
    $display_data['touch'] = 'false';
    $response = $this->postJson(route('displays.store'), $display_data);
    $this->assertDatabaseCount('displays', 0);
    $response->assertUnprocessable()->assertJsonValidationErrorFor('touch');
  }

  /**
   * @test
   * @dataProvider invalidDisplays
   */
  public function cant_store_invalid_display($invalidData, $invalidFields)
  {
    $this->postJson(route('displays.store'), $invalidData)
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('displays', 0);
  }

  public function invalidDisplays(): array
  {
    $display_data = ['name' => 'teste', 'size' => 42, 'width' => 1920, 'height' => 1080, 'touch' => true];
    return [
      [
        [...$display_data, 'touch' => 'false'], // string
        ['touch']
      ],
      [
        [...$display_data, 'touch' => 2], // number != 0/1
        ['touch']
      ],
      [
        [...$display_data, 'touch' => -1], // number != 0/1
        ['touch']
      ],
    ];
  }

}
