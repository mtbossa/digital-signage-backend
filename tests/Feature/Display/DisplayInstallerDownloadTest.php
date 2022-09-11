<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayInstallerDownloadTest extends TestCase
{
  use RefreshDatabase, DisplayTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();
  }

  /** @test */
  public function ensure_404_when_user_trying_to_access_installer_link()
  {
    $this->getJson(route('displays.installer.download', Display::factory()->create()->id))->assertNotFound();
  }

  /** @test */
  public function ensure_one_display_cannot_download_others_display_installer()
  {
    $correctDisplay = Display::factory()->create();
    $wrongDisplay = Display::factory()->create();
    $plainTextToken = $wrongDisplay->plainTextToken;
    $this->getJson(route('displays.installer.download', $correctDisplay->id),
      ["Authorization" => "Bearer $plainTextToken"])->assertNotFound();
  }


}
