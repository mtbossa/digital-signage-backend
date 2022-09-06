<?php

namespace Post\ScheduleWhenCreatingTest;

use App\Jobs\Post\StartPost;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class NonRecurrentDelayTest extends TestCase
{
  use RefreshDatabase, PostTestsTrait, AuthUserTrait;

  private int $displaysAmount = 3;
  private string $nowDate = '2022-01-01 15:15:00';

  /**
   * All values here are based on chosen $nowDate;
   */

  private array $queue = [
    [
      // Start date after today
      'start_date' => '2022-01-02', 'end_date' => '2022-01-03', 'start_time' => '15:14:00', 'end_time' => '15:16:00',
      'correctScheduleDate' => '2022-01-02 15:14:00'
    ],
    [
      // Start date after today
      'start_date' => '2022-05-20', 'end_date' => '2022-11-20', 'start_time' => '15:14:00', 'end_time' => '15:16:00',
      'correctScheduleDate' => '2022-05-20 15:14:00'
    ],
    [
      // Start date today, but start time not reached
      'start_date' => '2022-01-01', 'end_date' => '2022-11-20', 'start_time' => '15:16:00', 'end_time' => '15:17:00',
      'correctScheduleDate' => '2022-01-01 15:16:00'
    ],
    [
      // Start date today, but start time not reached and ends on next day
      'start_date' => '2022-01-01', 'end_date' => '2022-11-20', 'start_time' => '15:16:00', 'end_time' => '15:14:00',
      'correctScheduleDate' => '2022-01-01 15:16:00'
    ],
  ];

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->media = $this->_createMedia();
    $this->recurrence = $this->_createRecurrence();
    $this->post = $this->_createPost([
      'media_id' => $this->media->id,
      'recurrence_id' => $this->recurrence->id
    ]);
  }

  /**
   * @test
   * @dataProvider shouldQueue
   */
  public function when_creating_non_recurrent_post_should_queue_and_job_delay_must_be_correct(
    $startDate,
    $endDate,
    $startTime,
    $endTime,
    $correctScheduleDate
  ) {
    Bus::fake([StartPost::class]);

    $this->configureEventsTests($startDate, $endDate, $startTime, $endTime,
      $this->nowDate, $this->displaysAmount);

    Bus::assertDispatched(function (StartPost $job) use (
      $correctScheduleDate
    ) {
      $correctScheduleNextStartDate
        = Carbon::createFromTimeString($correctScheduleDate);

      // We can use now() because $this->configureEventsTests() called travelTo()
      $scheduledJobDate = now()->copy()->addSecond($job->delay);

      $this->assertCorrectJobScheduleDate($correctScheduleNextStartDate,
        $scheduledJobDate);

      return !is_null($job->delay);
    });
  }

  public function shouldQueue(): array
  {
    return $this->queue;
  }
}
