<?php

namespace Tests\Feature\DisplayPosts;

use App\Models\Display;
use App\Models\Media;
use App\Models\Raspberry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class AppDisplayPostTest extends TestCase
{
  use RefreshDatabase, AuthUserTrait;

  private Display $display;
  private Raspberry $raspberry;
  private Media $media;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->display = Display::factory()->create();
    $this->media = Media::factory()->create();
  }


}
