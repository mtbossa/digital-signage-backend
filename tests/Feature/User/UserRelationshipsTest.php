<?php

namespace Tests\Feature\User;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\Feature\User\Traits\UserTestsTrait;
use Tests\TestCase;

class UserRelationshipsTest extends TestCase
{
    use RefreshDatabase, UserTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
    }

    /** @test */
    public function a_user_might_belong_to_a_store()
    {
        $store = Store::factory()->create();
        $user = User::factory()->create(['store_id' => $store->id]);

        $this->assertInstanceOf(Store::class, $user->store);
        $this->assertEquals(1, $user->store->count());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'store_id' => $store->id]);
    }
}
