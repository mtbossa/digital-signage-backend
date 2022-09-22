<?php

namespace Tests\Feature\Post;

use App\Models\Recurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PostRecurrenceOptionsTest extends TestCase
{
  use RefreshDatabase, MediaTestsTrait, WithFaker, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function ensure_post_recurrence_options_is_returning_correct_amount()
  {
    $amount = 10;
    Recurrence::factory($amount)->create();

    $this->getJson(route('post.recurrence.options'))->assertOk()->assertJsonCount($amount);
  }

  /** @test */
  public function ensure_post_recurrence_options_structure_is_correct()
  {
    $recurrences = Recurrence::factory(2)->create();

    $correctStructure = $recurrences->map(function (Recurrence $recurrence) {
      return ['id' => $recurrence->id, 'description' => $recurrence->description];
    });

    $this->getJson(route('post.recurrence.options'))->assertExactJson($correctStructure->toArray());
  }
}
