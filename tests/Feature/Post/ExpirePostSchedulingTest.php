<?php

namespace Tests\Feature\Post;

use App\Jobs\ExpirePost;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class ExpirePostSchedulingTest extends TestCase
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
      'correctScheduleDate' => '2022-01-03 15:16:00'
    ],
    [
      // Start date after today
      'start_date' => '2022-05-20', 'end_date' => '2022-11-20', 'start_time' => '15:14:00', 'end_time' => '15:16:00',
      'correctScheduleDate' => '2022-11-20 15:16:00'
    ],
    [
      // Start date today, but start time not reached
      'start_date' => '2022-01-01', 'end_date' => '2022-11-20', 'start_time' => '15:16:00', 'end_time' => '15:17:00',
      'correctScheduleDate' => '2022-11-20 15:17:00'
    ],
    [
      // Start date today, but start time not reached and ends on next day
      'start_date' => '2022-01-01', 'end_date' => '2022-11-20', 'start_time' => '15:16:00', 'end_time' => '15:14:00',
      'correctScheduleDate' => '2022-11-20 15:14:00'
    ],
  ];

  public function setUp(): void
  {
    parent::setUp();

    Bus::fake();
    Event::fake();

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
   */
  public function when_creating_non_recurrent_post_should_schedule_expire_post_job()
  {
    Bus::fake(ExpirePost::class);

    $post_data = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id])->toArray();

    $this->postJson(route('posts.store'),
      [...$post_data, 'displays_ids' => null])->assertCreated();
    
    Bus::assertDispatchedTimes(ExpirePost::class, 1);
  }

  /**
   * @test
   * @dataProvider dates
   */
  public function when_creating_non_recurrent_post_should_schedule_expire_post_job_with_correct_schedule_date(
    $startDate,
    $endDate,
    $startTime,
    $endTime,
    $correctScheduleDate
  ) {
    Bus::fake(ExpirePost::class);
    $this->travelTo(Carbon::createFromFormat('Y-m-d H:i:s', $this->nowDate));

    $dates = [
      'start_date' => $startDate,
      'end_date' => $endDate,
      'start_time' => $startTime,
      'end_time' => $endTime
    ];
    $correctScheduleDateObject = Carbon::createFromFormat('Y-m-d H:i:s', $correctScheduleDate);

    $post_data = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id])->toArray();

    $this
      ->postJson(route('posts.store'), [...$post_data, ...$dates, 'displays_ids' => null])
      ->assertCreated();

    Bus::assertDispatched(ExpirePost::class, function (ExpirePost $job) use ($correctScheduleDateObject) {
      $scheduledDate = now()->copy()->addSeconds($job->delay);

      $this->assertCorrectJobScheduleDate($correctScheduleDateObject, $scheduledDate);

      return !is_null($job->delay);
    });
  }

  private function assertCorrectJobScheduleDate(
    Carbon $correct,
    Carbon $scheduled
  ): void {
    $this->assertTrue($scheduled->isSameDay($correct));
    $this->assertTrue($scheduled->isSameHour($correct));
    $this->assertTrue($scheduled->isSameMinute($correct));
  }

  public function dates(): array
  {
    return $this->queue;
  }
}
