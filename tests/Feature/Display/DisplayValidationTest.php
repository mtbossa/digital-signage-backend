<?php

namespace Tests\Feature\Display;

use App\Models\Display;
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

  /**
   * @test
   * @dataProvider invalidDisplays
   */
  public function cant_update_invalid_display($invalidData, $invalidFields)
  {
    $display = Display::factory()->create();

    $this->patchJson(route('displays.update', $display->id), [...$display->toArray(), ...$invalidData])
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();
  }


  public function invalidDisplays(): array
  {
    $display_data = [
      'name' => 'teste', 'size' => 42, 'width' => 1920, 'height' => 1080, 'observation' => 'a', 'pairing_code' => 'aaaaaa'
    ];
    return [
      'name greater than 100 char' => [[...$display_data, 'name' => Str::random(101)], ['name']],
      'name as null' => [[...$display_data, 'name' => null], ['name']],
      'observation as number' => [[...$display_data, 'observation' => 1], ['observation']],
      'observation as array' => [[...$display_data, 'observation' => []], ['observation']],
      'raspberry_id as string' => [[...$display_data, 'raspberry_id' => "a"], ['raspberry_id']],
      'raspberry_id as array' => [[...$display_data, 'raspberry_id' => []], ['raspberry_id']],
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
        'pairing_code as null' => [[...$display_data, 'pairing_code' => null], ['pairing_code']],
        'pairing_code as empty string' => [[...$display_data, 'pairing_code' => ''], ['pairing_code']],
        
    ];
  }

}
