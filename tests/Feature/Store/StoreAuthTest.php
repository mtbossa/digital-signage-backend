<?php

namespace Tests\Feature\Store;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class StoreAuthTest extends TestCase
{
    use RefreshDatabase, RaspberryTestsTrait, AuthUserTrait, DisplayTestsTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->display = $this->_createDisplay();
        $this->store
            = Store::factory()->create();
    }

    /** @test */
    public function when_creating_store_should_create_api_token_for_it()
    {
        $this->_authUser();
        $store = Store::factory()->make();

        $response = $this->postJson(route('stores.store'),
            $store->toArray())->assertCreated();

        $store = Store::find($response['id']);
        $this->assertCount(1, $store->tokens);
    }

    /** @test */
    public function when_creating_store_should_send_plain_text_api_token_in_response(
    )
    {
        $this->_authUser();
        $store = Store::factory()->make();

        $response = $this->postJson(route('stores.store'),
            $store->toArray())->assertCreated();

        $this->assertArrayHasKey('token', $response);
    }

    /** @test */
    public function authenticated_store_should_be_able_to_make_requests_to_store_displays_index_route(
    )
    {
        $response = $this->getJson(route('store.displays.index',
            ['store' => $this->store->id]),
            ['Authorization' => "Bearer {$this->store->plainTextToken}"])
            ->assertOk();
    }

    /** @test */
    public function unauthenticated_store_should_not_be_able_to_make_requests_to_store_displays_index_route(
    )
    {
        $this->getJson(route('store.displays.index',
            ['store' => $this->store->id]))->assertUnauthorized();
    }
}
