<?php

namespace Tests\Feature\PairingCode;

use App\Models\Display;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PairingCodeTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();
  }

  /** @test */
  public function should_generate_code_when_requested()
  {
    $response = $this->postJson(route('pairing-codes.store'));
    $response->assertCreated();
    $generated_code = $response->json('code');
    $this->assertDatabaseHas('pairing_codes', ['code' => $generated_code]);
  }
  
  
}
