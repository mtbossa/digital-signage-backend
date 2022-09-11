<?php

namespace Tests\Feature\Display;

use App\Mail\InstallationLink;
use App\Models\Display;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
    $display_data = $this->_makeDisplay()->toArray();

    $response = $this->postJson(route('displays.store'), $display_data);

    $this->assertDatabaseHas('displays', $display_data);

    $display = Display::find($response->json()['id']);
    $response->assertCreated()->assertJson($display->toArray());
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
  public function after_display_created_should_send_email_with_installation_link_to_display_creator()
  {
    Mail::fake();

    $display_data = Display::factory()->make()->toArray();
    $response = $this->postJson(route('displays.store'), $display_data);
    $response->assertCreated();

    Mail::assertQueued(InstallationLink::class);
  }

  /** @test */
  public function installer_email_should_have_correct_installer_download_url()
  {
    $this->withoutExceptionHandling();
    $display = Display::factory()->create();

    $mailable = new InstallationLink($display, $display->plainTextToken);
    $apiUrl = env('APP_URL');
    $correctUrl = url("{$apiUrl}/api/displays/{$display->plainTextToken}/installer/download");

    $mailable->assertSeeInHtml($correctUrl);
  }
}
