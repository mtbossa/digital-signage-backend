<?php

namespace Tests\Feature\Display;

use App\Models\Display;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
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

  /** @test */
  public function when_development_script_should_have_api_url_pusher_key_and_cluster()
  {
    $display = Display::factory()->create();
    $apiUrl = config("app.url");
    $cluster = config("broadcasting.connections.pusher.options.cluster");
    $pusherKey = config("broadcasting.connections.pusher.key");

    $response = $this->getJson(route('displays.installer.download', $display->id),
      ["Authorization" => "Bearer $display->plainTextToken"])->assertOk();
    $responseContent = $response->content();

    $this->assertStringContainsString("API_URL=$apiUrl", $responseContent);
    $this->assertStringContainsString("PUSHER_CLUSTER=$cluster", $responseContent);
    $this->assertStringContainsString("PUSHER_APP_KEY=$pusherKey", $responseContent);
  }

  /** @test */
  public function should_download_development_script_when_app_env_is_diff_from_prod_or_staging()
  {
    $display = Display::factory()->create();
    Config::set("app.env", "development");

    $response = $this->getJson(route('displays.installer.download', $display->id),
      ["Authorization" => "Bearer $display->plainTextToken"]);
    $responseContent = $response->content();
    $this->assertStringContainsString("NODE_ENV=development", $responseContent);

    Config::set("app.env", "local");

    $response = $this->getJson(route('displays.installer.download', $display->id),
      ["Authorization" => "Bearer $display->plainTextToken"]);
    $responseContent = $response->content();
    $this->assertStringContainsString("NODE_ENV=development", $responseContent);
  }

  /** @test */
  public function should_download_staging_script_when_app_env_is_staging()
  {
    $display = Display::factory()->create();
    Config::set("app.env", "staging");

    $response = $this->getJson(route('displays.installer.download', $display->id),
      ["Authorization" => "Bearer $display->plainTextToken"]);
    $responseContent = $response->content();
    $this->assertStringContainsString("NODE_ENV=staging", $responseContent);
  }

  /** @test */
  public function should_download_production_script_when_app_env_is_diff_from_prod_or_production()
  {
    $display = Display::factory()->create();
    Config::set("app.env", "production");

    $response = $this->getJson(route('displays.installer.download', $display->id),
      ["Authorization" => "Bearer $display->plainTextToken"]);
    $responseContent = $response->content();
    $this->assertStringContainsString("NODE_ENV=production", $responseContent);
  }
}
