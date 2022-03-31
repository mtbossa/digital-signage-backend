<?php

namespace Tests\Feature\Store;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Store\Traits\StoreTestsTrait;
use Tests\TestCase;
use Tests\Traits\AuthUserTrait;

class StoreTest extends TestCase
{
  use RefreshDatabase, StoreTestsTrait, WithFaker, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();

    $this->store = $this->_createStore();
  }

  /** @test */
  public function create_store()
  {
    $store_data = $this->_makeStore()->toArray();

    $this->postJson(route('stores.store'), $store_data)->assertCreated()->assertJson($store_data);

    $this->assertDatabaseHas('stores', $store_data);
  }

  /** @test */
  public function update_store()
  {
    $update_values = $this->_makeStore()->toArray();

    $response = $this->putJson(route('stores.update', $this->store->id), $update_values);

    $this->assertDatabaseHas('stores', $response->json());
    $response->assertJson($update_values)->assertOk();
  }

  /** @test */
  public function delete_store()
  {
    $response = $this->deleteJson(route('stores.destroy', $this->store->id));
    $this->assertDatabaseMissing('stores', ['id' => $this->store->id]);
    $response->assertOk();
  }

  /** @test */
  public function fetch_single_store()
  {
    $this->getJson(route('stores.show',
      $this->store->id))->assertOk()->assertJson($this->store->toArray());
  }

  /** @test */
  public function fetch_all_stores()
  {
    $this->_createStore();

    $this->getJson(route('stores.index'))->assertOk()->assertJsonCount(2)->assertJsonFragment($this->store->toArray());
  }
}
