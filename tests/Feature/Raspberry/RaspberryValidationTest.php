<?php

namespace Tests\Feature\Raspberry;

use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RaspberryValidationTest extends TestCase
{
  use RefreshDatabase, RaspberryTestsTrait, AuthUserTrait, WithFaker;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /**
   * @test
   * @dataProvider invalidRaspberries
   */
  public function cant_store_invalid_raspberry($invalidData, $invalidFields)
  {
    $raspberry_data = Raspberry::factory()->make()->toArray();
    $response = $this->postJson(route('raspberries.store'), [...$raspberry_data, ...$invalidData])
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('raspberries', 0);
  }

  public function invalidRaspberries(): array
  {
    return [
      'short_name greater than 30 char' => [['short_name' => Str::random(31)], ['short_name']],
      'short_name as null' => [['short_name' => null], ['short_name']],
      'short_name as empty string' => [['short_name' => ''], ['short_name']],
      'short_name as number' => [['short_name' => 1], ['short_name']],
      'mac_adress as null' => [['mac_address' => null], ['mac_address']],
      'mac_adress as empty string' => [['mac_address' => ''], ['mac_address']],
      'mac_adress as empty number' => [['mac_address' => 1], ['mac_address']],
      'mac_adress as random string' => [['mac_address' => Str::random(10)], ['mac_address']],
      'serial_number greater than 50 char' => [['serial_number' => Str::random(51)], ['serial_number']],
      'serial_number as null' => [['serial_number' => null], ['serial_number']],
      'serial_number as empty string' => [['serial_number' => ''], ['serial_number']],
      'serial_number as number' => [['serial_number' => 1], ['serial_number']],
      'observation as number' => [['observation' => 1], ['observation']],
    ];
  }
}
