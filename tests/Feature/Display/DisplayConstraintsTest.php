<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayConstraintsTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function observation_can_be_null()
  {
    Display::factory()->create(['observation' => null]);
    $this->assertDatabaseCount('displays', 1);
  }
}
