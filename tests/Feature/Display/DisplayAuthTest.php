<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayAuthTest extends TestCase
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
    public function when_creating_display_should_create_api_token_for_it()
    {
        $this->_authUser();
        $display = Display::factory()->make();

        $response = $this->postJson(route('displays.store'),
            $display->toArray())->assertCreated();

        $display = Display::find($response['id']);
        $this->assertCount(1, $display->tokens);
    }

    /** @test */
    public function when_creating_display_should_send_plain_text_api_token_in_response(
    )
    {
        $this->_authUser();
        $display = Display::factory()->make();

        $response = $this->postJson(route('displays.store'),
            $display->toArray())->assertCreated();

        $this->assertArrayHasKey('token', $response);
    }

    /** @test */
    public function authenticated_display_should_be_able_to_make_requests_to_display_posts_index_route(
    )
    {
        $response = $this->getJson(route('displays.posts.index',
            ['display' => $this->display->id]),
            ['Authorization' => "Bearer {$this->display->plainTextToken}"])
            ->assertOk();
    }
}
