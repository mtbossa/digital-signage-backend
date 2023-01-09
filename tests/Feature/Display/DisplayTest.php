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
    $created_display_id = $response->json()['id'];
    $this->assertDatabaseHas('displays', ['id' => $created_display_id, 'pairing_code_id' => $pairing_code->id]);

    $display = Display::find($created_display_id);
    $response->assertCreated()->assertJson($display->toArray());
  }

      /** @test */
    public function ensure_display_is_not_deleted_if_pairing_code_id_is_null_and_pairing_code_get_deleted()
    {
        $pairing_code = PairingCode::factory()->create();
        $display = Display::factory()->create();
        
        $pairing_code->delete();

        $this->assertModelExists($display);
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
