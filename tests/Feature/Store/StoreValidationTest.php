<?php

namespace Tests\Feature\Store;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Feature\Store\Traits\StoreTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class StoreValidationTest extends TestCase
{
  use RefreshDatabase, StoreTestsTrait, AuthUserTrait, WithFaker;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function store_name_must_be_unique_when_creating()
  {
    $this->store = $this->_createStore();
    $store = $this->_makeStore(['name' => $this->store->name]);
    $this->postJson(route('stores.store'),
      $store->toArray())->assertUnprocessable()->assertJsonValidationErrorFor('name');
  }

  /** @test */
  public function store_name_must_be_unique_when_updating()
  {
    $this->store = $this->_createStore();
    $updatableStore = Store::factory()->create();
    $this->patchJson(route('stores.update', $updatableStore->id),
      ['name' => $this->store->name])->assertUnprocessable()->assertJsonValidationErrorFor('name');
  }

  /**
   * @test
   * @dataProvider invalidStores
   */
  public function cant_store_invalid_store($invalidData, $invalidFields)
  {
    $this->postJson(route('stores.store'), $invalidData)
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('stores', 0);
  }

  /**
   * @test
   * @dataProvider invalidStores
   */
  public function cant_update_invalid_store($invalidData, $invalidFields)
  {
    $store = Store::factory()->create();
    $this->patchJson(route('stores.update', $store->id), [...$store->toArray(), ...$invalidData])
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();
  }

  public function invalidStores(): array
  {
    $store_data = ['name' => Str::random(20)];
    return [
      'name greater than 255 char' => [[...$store_data, 'name' => Str::random(256)], ['name']],
      'name as null' => [[...$store_data, 'name' => null], ['name']],
      'name as empty string' => [[...$store_data, 'name' => ''], ['name']],
    ];
  }

}
