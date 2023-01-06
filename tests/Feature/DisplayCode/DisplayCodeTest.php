<?php

namespace Tests\Feature\DisplayCode;

use App\Models\Display;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayCodeTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();
  }

  /** @test */
  public function should_generate_code_when_requested()
  {
    $response = $this->postJson(route('displays-codes.store'));
    $response->assertCreated();
    $generated_code = $response->json('code');
    $this->assertDatabaseHas('displays_codes', ['code' => $generated_code]);
  }
  
  
}
