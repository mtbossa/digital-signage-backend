<?php

namespace Tests\Feature\Recurrence;

use App\Models\Recurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Feature\Recurrence\Traits\RecurrenceTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RecurrenceValidationTest extends TestCase
{
  use RefreshDatabase, RecurrenceTestsTrait, AuthUserTrait, WithFaker;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /**
   * @test
   * @dataProvider invalidRecurrences
   */
  public function cant_store_invalid_recurrence($invalidData, $invalidFields)
  {
    $response = $this->postJson(route('recurrences.store'), $invalidData)
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseCount('recurrences', 0);
  }

  public function invalidRecurrences(): array
  {
    $recurrence_data = ['description' => Str::random(20), 'isoweekday' => 1, 'day' => 1, 'month' => 1, 'year' => 2022];
    return [
      'description greater than 50 char' => [[...$recurrence_data, 'description' => Str::random(51)], ['description']],
      'description as null' => [[...$recurrence_data, 'description' => null], ['description']],
      'description as empty string' => [[...$recurrence_data, 'description' => ''], ['description']],
      'all dates null' => [[...$recurrence_data, 'isoweekday' => null, 'day' => null, 'month' => null, 'year' => null], ['isoweekday', 'day', 'month', 'year']],
      'isoweekday less than 1' => [[...$recurrence_data, 'isoweekday' => 0], ['isoweekday']],
      'isoweekday greather than 7' => [[...$recurrence_data, 'isoweekday' => 8], ['isoweekday']],
      'isoweekday as string' => [[...$recurrence_data, 'isoweekday' => 'a'], ['isoweekday']],
      'day as less than 1' => [[...$recurrence_data, 'day' => 0], ['day']],
      'day greather than 31' => [[...$recurrence_data, 'day' => 32], ['day']],
      'day as string' => [[...$recurrence_data, 'day' => 'a'], ['day']],
      'month as less than 1' => [[...$recurrence_data, 'month' => 0], ['month']],
      'month greather than 12' => [[...$recurrence_data, 'month' => 13], ['month']],
      'month as string' => [[...$recurrence_data, 'month' => 'a'], ['month']],
      'year less than 2000' => [[...$recurrence_data, 'year' => 1999], ['year']],
      'year greater than 2500' => [[...$recurrence_data, 'year' => 2501], ['year']],
      'year as string' => [[...$recurrence_data, 'year' => 'a'], ['year']],
    ];
  }

  /**
   * @test
   * @dataProvider invalidUpdateRecurrences
   */
  public function cant_update_invalid_recurrence($invalidData, $invalidFields)
  {
    $recurrence = Recurrence::factory()->create();
    $response = $this->putJson(route('recurrences.update', $recurrence->id), [...$recurrence->toArray(), ...$invalidData])
      ->assertJsonValidationErrors($invalidFields)
      ->assertUnprocessable();

    $this->assertDatabaseHas('recurrences', ['id' => $recurrence->id, 'description' => $recurrence->description]);
  }

  public function invalidUpdateRecurrences(): array
  {
    return [
      'description greater than 50 char' => [['description' => Str::random(51)], ['description']],
      'description as null' => [['description' => null], ['description']],
      'description as empty string' => [['description' => ''], ['description']],
    ];
  }

}
