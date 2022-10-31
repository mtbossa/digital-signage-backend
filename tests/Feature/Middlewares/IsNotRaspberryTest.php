<?php

namespace Tests\Feature\Middlewares;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class IsNotRaspberryTest extends TestCase
{
  use RefreshDatabase, AuthUserTrait, RaspberryTestsTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->raspberry = $this->_createRaspberry();
  }

  /**
   * @test
   */
  public function ensure_raspberry_cant_access_routes_inside_isNotRaspberry_middleware()
  {
    $response = $this->getJson(route('stores.index'),
      ["Authorization" => "Bearer {$this->raspberry->plainTextToken}"])->assertUnauthorized();
    $response = $this->getJson(route('displays.index'),
      ["Authorization" => "Bearer {$this->raspberry->plainTextToken}"])->assertUnauthorized();
  }

  /**
   * @test
   */
  public function assert_unauthenticated_response_is_correct()
  {
    $response = $this->getJson(route('stores.index'),
      ["Authorization" => "Bearer {$this->raspberry->plainTextToken}"])->assertJson(["message" => "Unauthenticated."]);
  }
}
