<?php

namespace Tests\Feature\Store;

use App\Models\Display;
use App\Models\Raspberry;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Store\Traits\StoreTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class StoreRelationshipsTest extends TestCase
{
  use RefreshDatabase, StoreTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function a_store_might_have_many_users()
  {
    $user_amount = 3;
    $store = Store::factory()->create();
    User::factory()->create();
    $users = User::factory($user_amount)->create(['store_id' => $store->id]);

    $this->assertInstanceOf(User::class, $store->users[0]);
    $this->assertEquals($user_amount, $store->users->count());
    $this->assertDatabaseHas('users', ['id' => $users[0]->id, 'store_id' => $store->id]);
  }
  
  /** @test */
  public function a_store_might_have_many_displays()
  {
    $displays_amount = 3;
    $store = Store::factory()->create();
    Display::factory()->create();
    $displays = Display::factory($displays_amount)->create(['store_id' => $store->id]);

    $this->assertInstanceOf(Display::class, $store->displays[0]);
    $this->assertEquals($displays_amount, $store->displays->count());
    $this->assertDatabaseHas('displays', ['id' => $displays[0]->id, 'store_id' => $store->id]);
  }
}
