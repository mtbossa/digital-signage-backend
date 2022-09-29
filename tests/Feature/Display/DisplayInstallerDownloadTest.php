<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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
    Sanctum::actingAs(User::factory()->create());
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

  /** @test */
  public function ensure_installer_content_has_api_key_and_display_id()
  {
    $display = Display::factory()->create();
    $apiUrl = config("app.url");
    $githubRepoUrl = config("app.app_github_repo_url");

    $response = $this->getJson(route('displays.installer.download', $display->id),
      ["Authorization" => "Bearer $display->plainTextToken"])->assertOk();
    $responseContent = $response->content();

    $this->assertStringContainsString("DISPLAY_ID={$display->id}", $responseContent);
    $this->assertStringContainsString("DISPLAY_API_TOKEN=\"{$display->plainTextToken}\"", $responseContent);
    $this->assertStringContainsString("APP_GITHUB_REPO_URL={$githubRepoUrl}", $responseContent);
  }
}
