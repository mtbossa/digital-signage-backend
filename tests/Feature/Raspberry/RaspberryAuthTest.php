<?php

namespace Tests\Feature\Raspberry;

use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RaspberryAuthTest extends TestCase
{
  use RefreshDatabase, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->raspberry = Raspberry::factory()->create();
  }

  /** @test */
  public function when_creating_raspberry_should_create_api_token_for_it()
  {
    $this->_authUser();
    $raspberry = Raspberry::factory()->make();

    $response = $this->postJson(route('raspberries.store'),
      $raspberry->toArray())->assertCreated();

    $raspberry = Raspberry::find($response['id']);
    $this->assertCount(1, $raspberry->tokens);
  }

  /** @test */
  public function when_creating_raspberry_should_send_plain_text_api_token_in_response()
  {
    $this->_authUser();
    $raspberry = Raspberry::factory()->make();

    $response = $this->postJson(route('raspberries.store'),
      $raspberry->toArray())->assertCreated();

    $this->assertArrayHasKey('token', $response);
  }
}
