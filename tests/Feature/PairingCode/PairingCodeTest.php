<?php

namespace Tests\Feature\PairingCode;

use App\Jobs\ExpirePairingCode;
use App\Models\PairingCode;
use App\Services\PairingCodeGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class PairingCodeTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();
    
    Bus::fake([ExpirePairingCode::class]);
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
    public function should_have_expire_at_with_five_minutes_from_now()
    {
        $response = $this->postJson(route('pairing-codes.store'));
        $response->assertCreated();
        $pairing_code = $response->json();
        $expected_expires_at = now()->addMinutes(5)->toIso8601String();
        $this->assertDatabaseHas('pairing_codes', ['code' => $pairing_code['code'], 'expires_at' => $expected_expires_at]);
    }

  /** @test */
  public function should_generate_new_code_if_generated_an_already_existing_one()
  {
      $already_generated = Str::repeat('a', 6);
      $new_generated = Str::repeat('b', 6);
      $expires_at = now()->addMinutes(5);

      PairingCode::create(["code" => $already_generated, 'expires_at' => $expires_at]);

      $this->partialMock(PairingCodeGeneratorService::class,
          function (MockInterface $mock) use ($already_generated, $new_generated, $expires_at) {
              $mock->shouldReceive('generate')->times(2)->andReturn([
                  'code' => $already_generated, 'expires_at' => $expires_at
              ], ['code' => $new_generated, 'expires_at' => $expires_at]);
          });

      $this->postJson(route('pairing-codes.store'))->assertCreated();
      $this->assertDatabaseHas('pairing_codes', ['code' => $new_generated]);
  }

    /** @test */
    public function if_tried_more_then_100_times_should_return_503_response()
    {
        $already_generated = Str::repeat('a', 6);
        $expires_at = now()->addMinutes(5);

        PairingCode::create(["code" => $already_generated, 'expires_at' => $expires_at]);

        $this->partialMock(PairingCodeGeneratorService::class, function (MockInterface $mock) use ($already_generated, $expires_at) {
            $mock->shouldReceive('generate')->andReturn(['code' => $already_generated, 'expires_at' => $expires_at]);
        });

        $this->postJson(route('pairing-codes.store'))->assertStatus(503);
        $this->assertDatabaseCount('pairing_codes', 1);
    }
    
    /** @test */
    public function should_schedule_expire_with_expires_at_datetime()
    {
        Bus::fake([ExpirePairingCode::class]);
        
        $response = $this->postJson(route('pairing-codes.store'));
        $response->assertCreated();
        $code = $response->json('code');
        
        $pairing_code = PairingCode::query()->where('code', $code)->first();

        Bus::assertDispatchedTimes(ExpirePairingCode::class, 1);
        Bus::assertDispatched(ExpirePairingCode::class, function (ExpirePairingCode $job) use ($pairing_code) {
            return $pairing_code->id === $job->pairing_code->id;
        });
        Bus::assertDispatched(ExpirePairingCode::class, function (ExpirePairingCode $job) use ($pairing_code) {
            return $job->delay === $pairing_code->expires_at;
        });
    }
  
}
