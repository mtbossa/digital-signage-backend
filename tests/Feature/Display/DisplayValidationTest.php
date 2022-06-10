<?php

namespace Tests\Feature\Display;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayValidationTest extends TestCase
{
  use RefreshDatabase, DisplayTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }
  
  /** @test */
  public function touch_if_false_by_default()
  {
    $display_data = $this->_makeDisplay(['touch' => null])->toArray();
    unset($display_data['touch']);

    $this->postJson(route('displays.store'), $display_data);

    $this->assertDatabaseCount('displays', 1);
    $this->assertDatabaseHas('displays', ['touch' => false]);
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
      'name greater than 100 char' => [[...$display_data, 'name' => Str::random(101)], ['name']],
      'name as null' => [[...$display_data, 'name' => null], ['name']],
      'size as null' => [[...$display_data, 'size' => null], ['size']],
      'size as string' => [[...$display_data, 'size' => 'a'], ['size']],
      'size 0' => [[...$display_data, 'size' => 0], ['size']],
      'size greater then 1000' => [[...$display_data, 'size' => 1001], ['size']],
      'width as null' => [[...$display_data, 'width' => null], ['width']],
      'width as string' => [[...$display_data, 'width' => 'a'], ['width']],
      'width 0' => [[...$display_data, 'width' => 0], ['width']],
      'width greater then 20000' => [[...$display_data, 'width' => 20001], ['width']],
      'height as null' => [[...$display_data, 'height' => null], ['height']],
      'height as string' => [[...$display_data, 'height' => 'a'], ['height']],
      'height 0' => [[...$display_data, 'height' => 0], ['height']],
      'height greater then 20000' => [[...$display_data, 'height' => 20001], ['height']],
      'touch as string' =>
        [
          [...$display_data, 'touch' => 'false'], // string
          ['touch']
        ],
      'touch greater than 1' =>
        [
          [...$display_data, 'touch' => 2], // number != 0/1
          ['touch']
        ],
      'touch lower than 0' =>
        [
          [...$display_data, 'touch' => -1], // number != 0/1
          ['touch']
        ],
    ];
  }

}
