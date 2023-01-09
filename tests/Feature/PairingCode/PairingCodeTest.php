<?php

namespace Tests\Feature\PairingCode;

use App\Jobs\ExpirePairingCode;
use App\Models\Display;
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

    /** @test */
    public function should_generate_api_token_for_display()
    {
        $pairing_code = PairingCode::factory()->create();
        $display = Display::factory()->create(['pairing_code_id' => $pairing_code->id]);

        $response = $this->patchJson(route('pairing-codes.update', ['pairing_code' => $pairing_code->code]));
        $response->assertOk()->assertJsonStructure(['api_token', 'display_id']);
        
        $token = $response->json('api_token');
        $display_id = $response->json('display_id');
        
        $this->assertIsString($token);
        $this->assertEquals($display->id, $display_id);
    }

    /** @test */
    public function should_delete_pairing_code_after_generating_api_token()
    {
        $pairing_code = PairingCode::factory()->create();
        $display = Display::factory()->create(['pairing_code_id' => $pairing_code->id]);

        $response = $this->patchJson(route('pairing-codes.update', ['pairing_code' => $pairing_code->code]));
        $response->assertOk();
        
        $this->assertModelMissing($pairing_code);
        $this->assertDatabaseHas('displays', ['id' => $display->id, 'pairing_code_id' => null]);
    }

    /** @test */
    public function expect_404_to_be_returned_if_cant_find_pairing_code()
    {
        $this->patchJson(route('pairing-codes.update', ['pairing_code' => 'aaaaaa']))->assertNotFound();
    }

    /** @test */
    public function expect_422_to_be_returned_if_pairing_code_doesnt_have_a_display_yet()
    {
        $pairing_code = PairingCode::factory()->create();
        $this->patchJson(route('pairing-codes.update', ['pairing_code' => $pairing_code->code]))->assertUnprocessable();
    }
}
