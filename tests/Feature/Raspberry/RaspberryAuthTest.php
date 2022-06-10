<?php

namespace Tests\Feature\Raspberry;

use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RaspberryAuthTest extends TestCase
{
  use RefreshDatabase, RaspberryTestsTrait, AuthUserTrait, DisplayTestsTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->display = $this->_createDisplay();
    $this->raspberry = $this->_createRaspberry(['display_id' => $this->display->id]);
  }

  /** @test */
  public function when_creating_raspberry_should_create_api_token_for_it()
  {
    $this->_authUser();
    $display = $this->_createDisplay();
    $raspberry = $this->_makeRaspberry(['display_id' => $display->id]);

    $response = $this->postJson(route('raspberries.store'), $raspberry->toArray())->assertCreated();

    $raspberry = Raspberry::find($response['id']);
    $this->assertCount(1, $raspberry->tokens);
  }

  /** @test */
  public function when_creating_raspberry_should_send_plain_text_api_token_in_response()
  {
    $this->_authUser();
    $display = $this->_createDisplay();
    $raspberry = $this->_makeRaspberry(['display_id' => $display->id]);
    $response = $this->postJson(route('raspberries.store'), $raspberry->toArray())->assertCreated();

    $this->assertArrayHasKey('token', $response);
  }

  /** @test */
  public function authenticated_raspberry_should_be_able_to_make_requests_to_displays_posts_index_route()
  {
    $response = $this->getJson(route('displays.posts.index',
      ['display' => $this->raspberry->display->id]),
      ['Authorization' => "Bearer {$this->raspberry->plainTextToken}"])->assertOk();
  }

  /** @test */
  public function unauthenticated_raspberry_should_not_be_able_to_make_requests_to_displays_posts_index_route()
  {
    $this->getJson(route('displays.posts.index',
      ['display' => $this->raspberry->display->id]))->assertUnauthorized();
  }
}
