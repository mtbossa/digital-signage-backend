<?php


namespace Tests\Feature\PairingCode\Jobs;

use App\Jobs\ExpirePairingCode;
use App\Jobs\ExpirePost;
use App\Models\PairingCode;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class ExpirePairingCodeTest extends TestCase
{
  use RefreshDatabase, PostTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /**
   * @test
   */
  public function should_delete_pairing_code()
  {
    $pairing_code = PairingCode::factory()->create();
    $this->assertModelExists($pairing_code);
    ExpirePairingCode::dispatch($pairing_code);
    $this->assertModelMissing($pairing_code);
  }
}
