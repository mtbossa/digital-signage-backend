<?php

namespace Tests\Feature\Recurrence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Recurrence\Traits\RecurrenceTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RecurrenceTest extends TestCase
{
  use RefreshDatabase, RecurrenceTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->recurrence = $this->_createRecurrence();
  }

  /** @test */
  public function create_recurrence()
  {
    $recurrence_data = $this->_makeRecurrence()->toArray();

    $response = $this->postJson(route('recurrences.store'), $recurrence_data);

    $this->assertDatabaseHas('recurrences', $recurrence_data);

    $response->assertCreated()->assertJson($recurrence_data);
  }

  /** @test */
  public function update_recurrence_description()
  {
    $update_values = ['description' => 'Atualizando descrição'];

    $response = $this->putJson(route('recurrences.update', $this->recurrence->id), $update_values);

    $this->assertDatabaseHas('recurrences', $response->json());
    $response->assertJson($update_values)->assertOk();
  }

  /** @test */
  public function ensure_only_description_is_updated_even_if_more_fields_are_sent()
  {
    $current_values = $this->recurrence->toArray();
    unset($current_values['description']);
    $update_values = $this->_makeRecurrence()->toArray();

    $this->putJson(route('recurrences.update', $this->recurrence->id), $update_values)->assertJson(['description' => $update_values['description']])->assertOk();
    $this->assertDatabaseHas('recurrences', ['id' => $this->recurrence->id, 'description' => $update_values['description']]);
  }

  /** @test */
  public function delete_recurrence()
  {
    $response = $this->deleteJson(route('recurrences.destroy', $this->recurrence->id));
    $this->assertDatabaseMissing('recurrences', ['id' => $this->recurrence->id]);
    $response->assertOk();
  }

  /** @test */
  public function fetch_single_recurrence()
  {
    $this->getJson(route('recurrences.show',
      $this->recurrence->id))->assertOk()->assertJson($this->recurrence->toArray());
  }

  /** @test */
  public function fetch_all_recurrences()
  {
    $this->_createRecurrence();

    $this->getJson(route('recurrences.index'))->assertOk()->assertJsonCount(2,
      "data")->assertJsonFragment($this->recurrence->toArray());
  }
}
