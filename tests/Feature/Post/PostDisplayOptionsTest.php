<?php

namespace Tests\Feature\Post;

use App\Models\Display;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PostDisplayOptionsTest extends TestCase
{
  use RefreshDatabase, MediaTestsTrait, WithFaker, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function ensure_post_display_options_is_returning_correct_amount()
  {
    $amount = 10;
    Display::factory($amount)->create();

    $this->getJson(route('post.display.options'))->assertOk()->assertJsonCount($amount);
  }

  /** @test */
  public function ensure_post_media_options_structure_is_correct()
  {
    $displays = Display::factory(2)->create();

    $correctStructure = $displays->map(function (Display $display) {
      return ['id' => $display->id, 'name' => $display->name];
    });

    $this->getJson(route('post.display.options'))->assertExactJson($correctStructure->toArray());
  }
}
