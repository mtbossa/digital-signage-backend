<?php

namespace Tests\Feature\PairingCode;

use App\Models\PairingCode;
use App\Services\PairingCodeGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery\MockInterface;
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

  /** @test */
  public function should_generate_new_code_if_generated_an_already_existing_one()
  {
    $already_generated = Str::repeat('a', 6);
    $new_generated = Str::repeat('b', 6);
    
    PairingCode::create(["code" => $already_generated]);

    $this->partialMock(PairingCodeGeneratorService::class, function (MockInterface $mock) use ($already_generated, $new_generated) {
      $mock->shouldReceive('generate')->once()->andReturn($already_generated);
      $mock->shouldReceive('generate')->once()->andReturn($new_generated);
    });
    
    $this->postJson(route('pairing-codes.store'))->assertCreated();
    $this->assertDatabaseHas('pairing_codes', ['code' => $new_generated]);
  }

    /** @test */
    public function if_tried_more_then_100_times_should_return_503_response()
    {
        $already_generated = Str::repeat('a', 6);

        PairingCode::create(["code" => $already_generated]);

        $this->partialMock(PairingCodeGeneratorService::class, function (MockInterface $mock) use ($already_generated) {
            $mock->shouldReceive('generate')->andReturn($already_generated);
        });

        $this->postJson(route('pairing-codes.store'))->assertStatus(503);
        $this->assertDatabaseCount('pairing_codes', 1);
    }
  
}