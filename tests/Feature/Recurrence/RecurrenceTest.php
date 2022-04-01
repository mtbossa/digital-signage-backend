<?php

namespace Tests\Feature\recurrence;

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

    $this->getJson(route('recurrences.index'))->assertOk()->assertJsonCount(2)->assertJsonFragment($this->recurrence->toArray());
  }
}
