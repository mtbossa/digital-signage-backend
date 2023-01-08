<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use App\Models\PairingCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayTest extends TestCase
{
  use RefreshDatabase, DisplayTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->display = $this->_createDisplay();
  }

  /** @test */
  public function create_display()
  {
      $pairing_code = PairingCode::factory()->create();
      
    $display_data = $this->_makeDisplay()->toArray();

    $response = $this->postJson(route('displays.store'), [...$display_data, 'pairing_code' => $pairing_code->code]);

    $this->assertDatabaseHas('displays', $display_data);

    $display = Display::find($response->json()['id']);
    $response->assertCreated()->assertJson($display->toArray());
  }

    /** @test */
    public function ensure_pairing_code_is_deleted_after_display_creation()
    {
        $pairing_code = PairingCode::factory()->create();
        $display_data = $this->_makeDisplay()->toArray();
        
        $this->postJson(route('displays.store'), [...$display_data, 'pairing_code' => $pairing_code->code])->assertCreated();
        
        $this->assertModelMissing($pairing_code);
    }

  /** @test */
  public function update_display()
  {
    $update_values = $this->_makeDisplay()->toArray();

    $response = $this->putJson(route('displays.update', $this->display->id), $update_values);


    $this->assertDatabaseHas('displays', $update_values);
    $response->assertJson($update_values)->assertOk();
  }

  /** @test */
  public function delete_display()
  {
    $response = $this->deleteJson(route('displays.destroy', $this->display->id));
    $this->assertDatabaseMissing('displays', ['id' => $this->display->id]);
    $response->assertOk();
  }

  /** @test */
  public function fetch_single_display()
  {
    $this->getJson(route('displays.show', $this->display->id))->assertOk()->assertJsonFragment(['id' => $this->display->id]);
  }

  /** @test */
  public function fetch_all_displays()
  {
    $display = $this->_createDisplay();

    Display::all(['id'])->toArray();

    $this->getJson(route('displays.index'))->assertOk()->assertJsonCount(2,
      'data')->assertJsonFragment(['id' => $display->id]);
  }

    /** @test */
    public function ensure_422_when_trying_to_create_display_with_not_existing_pairing_code()
    {
        $display_data = $this->_makeDisplay()->toArray();
        $this->postJson(route('displays.store'), [...$display_data, 'pairing_code' => 'aaaaaa'])->assertUnprocessable();
    }
}
