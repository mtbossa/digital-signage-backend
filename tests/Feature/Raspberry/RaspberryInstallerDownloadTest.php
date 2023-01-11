<?php

namespace Tests\Feature\Raspberry;

use App\Models\Raspberry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Raspberry\Traits\RaspberryTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RaspberryInstallerDownloadTest extends TestCase
{
    use RefreshDatabase, RaspberryTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function ensure_404_when_user_trying_to_access_installer_link()
    {
        Sanctum::actingAs(User::factory()->create());
        $this->getJson(route('raspberry.installer.download'))->assertNotFound();
    }

    /** @test */
    public function ensure_installer_content_has_correct_needed_content()
    {
        $raspberry = Raspberry::factory()->create();
        $githubRepoUrl = config('app.app_github_repo_url');

        $response = $this->getJson(route('raspberry.installer.download'),
            ['Authorization' => "Bearer $raspberry->plainTextToken"])->assertOk();
        $responseContent = $response->content();

        $this->assertStringContainsString("RASPBERRY_ID={$raspberry->id}", $responseContent);
        $this->assertStringContainsString("RASPBERRY_API_TOKEN=\"{$raspberry->plainTextToken}\"", $responseContent);
        $this->assertStringContainsString("APP_GITHUB_REPO_URL={$githubRepoUrl}", $responseContent);
    }

    /** @test */
    public function when_development_script_should_have_api_url_pusher_key_and_cluster()
    {
        $raspberry = Raspberry::factory()->create();
        $apiUrl = config('app.url');
        $cluster = config('broadcasting.connections.pusher.options.cluster');
        $pusherKey = config('broadcasting.connections.pusher.key');

        $response = $this->getJson(route('raspberry.installer.download', $raspberry->id),
            ['Authorization' => "Bearer $raspberry->plainTextToken"])->assertOk();
        $responseContent = $response->content();

        $this->assertStringContainsString("API_URL=$apiUrl", $responseContent);
        $this->assertStringContainsString("PUSHER_CLUSTER=$cluster", $responseContent);
        $this->assertStringContainsString("PUSHER_APP_KEY=$pusherKey", $responseContent);
    }

    /** @test */
    public function should_download_development_script_when_app_env_is_diff_from_prod_or_staging()
    {
        $raspberry = Raspberry::factory()->create();
        Config::set('app.env', 'development');

        $response = $this->getJson(route('raspberry.installer.download', $raspberry->id),
            ['Authorization' => "Bearer $raspberry->plainTextToken"]);
        $responseContent = $response->content();
        $this->assertStringContainsString('NODE_ENV=development', $responseContent);

        Config::set('app.env', 'local');

        $response = $this->getJson(route('raspberry.installer.download', $raspberry->id),
            ['Authorization' => "Bearer $raspberry->plainTextToken"]);
        $responseContent = $response->content();
        $this->assertStringContainsString('NODE_ENV=development', $responseContent);
    }

    /** @test */
    public function should_download_staging_script_when_app_env_is_staging()
    {
        $raspberry = Raspberry::factory()->create();
        Config::set('app.env', 'staging');

        $response = $this->getJson(route('raspberry.installer.download', $raspberry->id),
            ['Authorization' => "Bearer $raspberry->plainTextToken"]);
        $responseContent = $response->content();
        $this->assertStringContainsString('NODE_ENV=staging', $responseContent);
    }

    /** @test */
    public function should_download_production_script_when_app_env_is_diff_from_prod_or_production()
    {
        $raspberry = Raspberry::factory()->create();
        Config::set('app.env', 'production');

        $response = $this->getJson(route('raspberry.installer.download', $raspberry->id),
            ['Authorization' => "Bearer $raspberry->plainTextToken"]);
        $responseContent = $response->content();
        $this->assertStringContainsString('NODE_ENV=production', $responseContent);
    }
}
