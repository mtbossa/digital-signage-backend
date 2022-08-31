<?php


namespace Post\Listeners\SchedulePostStartTest;

use App\Events\Post\ShouldEndPost;
use App\Jobs\Post\StartPost;
use App\Models\Display;
use App\Models\Post;
use App\Models\Raspberry;
use App\Notifications\Post\PostExpired;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class NonRecurrentTest extends TestCase
{
    use RefreshDatabase, PostTestsTrait, AuthUserTrait;

    private int $displaysAmount = 3;
    private string $nowDate = '2022-01-01 15:15:00';

    /**
     * All values here are based on chosen $nowDate;
     */

    // Between these times, must always dispatch event
    private array $eventTimesOneDay
        = [
            ['start' => '15:14:00', 'end' => '15:16:00'],
            ['start' => '14:14:00', 'end' => '15:16:00'],
            ['start' => '15:14:00', 'end' => '16:16:00'],
        ];

    private array $eventTimesTwoDay
        = [
            ['start' => '05:10:00', 'end' => '04:10:00'],
            // start one day, finishes tomorrow
            ['start' => '16:14:00', 'end' => '16:13:00'],
            // start one day, finishes tomorrow
            ['start' => '15:14:00', 'end' => '15:13:00'],
            // start one day, finishes tomorrow
        ];

    // Between these times, must always place in the queue
    private array $queueTimesOneDay
        = [
            ['start' => '15:16:00', 'end' => '15:17:00'],
            ['start' => '15:13:00', 'end' => '15:14:00'],
        ];

    private array $queueTimesTwoDay
        = [
            ['start' => '16:10:00', 'end' => '15:14:00'],
        ];

    // Between these dates, could either queue or dispatch event
    // because the time will choose what must be done
    private array $showDates
        = [
            ['start' => '2021-12-31', 'end' => '2022-01-02'],
            ['start' => '2021-10-05', 'end' => '2022-05-06'],
            ['start' => '2022-01-01', 'end' => '2022-01-02'],
        ];

    // Always will queue when start date is after today
    private array $queueDates
        = [
            ['start' => '2022-01-02', 'end' => '2022-01-03'],
            ['start' => '2022-05-20', 'end' => '2022-11-20'],
        ];

    // Always will queue when start date is after today
    private array $expireDates
        = [
            ['start' => '2022-01-01', 'end' => '2022-01-01'],
            ['start' => '2021-12-29', 'end' => '2022-01-01'],
            ['start' => '2021-12-31', 'end' => '2022-01-01'],
        ];

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
        $this->media = $this->_createMedia();
        $this->recurrence = $this->_createRecurrence();
        $this->post = $this->_createPost([
            'media_id'      => $this->media->id,
            'recurrence_id' => $this->recurrence->id
        ]);
    }


    public function oneDayPostsThatShouldDispatchEvent(): array
    {
        $test = [];
        foreach ($this->eventTimesOneDay as $eventTime) {
            foreach ($this->showDates as $showDate) {
                $startDate = $showDate['start'];
                $startTime = $eventTime['start'];
                $endDate = $showDate['end'];
                $endTime = $eventTime['end'];
                $startDateAndTime = "$startDate $startTime";
                $endDateAndTime = "$endDate $endTime";

                $string = "Start: $startDateAndTime | End: $endDateAndTime";

                $test[$string] = [
                    'startDate' => $showDate['start'],
                    'endDate'   => $showDate['end'],
                    'startTime' => $eventTime['start'],
                    'endTime'   => $eventTime['end']
                ];
            }
        }
        return $test;
    }

    public function twoDayPostsThatShouldDispatchEvent(): array
    {
        $test = [];
        foreach ($this->eventTimesTwoDay as $eventTime) {
            foreach ($this->showDates as $showDate) {
                $startDate = $showDate['start'];
                $startTime = $eventTime['start'];
                $endDate = $showDate['end'];
                $endTime = $eventTime['end'];
                $startDateAndTime = "$startDate $startTime";
                $endDateAndTime = "$endDate $endTime";

                $string = "Start: $startDateAndTime | End: $endDateAndTime";

                $test[$string] = [
                    'startDate' => $showDate['start'],
                    'endDate'   => $showDate['end'],
                    'startTime' => $eventTime['start'],
                    'endTime'   => $eventTime['end']
                ];
            }
        }
        return $test;
    }

    /**
     * @test
     * @dataProvider oneDayPostsThatShouldDispatchEvent
     */
    public function one_day_post_must_dispatch_start_post_job_for_next_day_start_time(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        Bus::fake([StartPost::class]);

        $post = Post::factory()->create([
            'start_date' => $startDate, 'start_time' => $startTime,
            'end_date'   => $endDate, 'end_time' => $endTime,
            'media_id'   => $this->media->id
        ]);

        // subDay() so post won't expire
        $this->travelTo(Carbon::createFromFormat('Y-m-d H:i:s',
            $endDate.$endTime)->subDay());

        event(new ShouldEndPost($post));

        Bus::assertDispatched(StartPost::class, 1);
        Bus::assertDispatched(function (StartPost $job) use (
            $endTime,
            $startTime
        ) {
            $correctScheduleNextStartDate
                = Carbon::createFromTimeString($startTime)->addDay();
            $endTimeObject = Carbon::createFromTimeString($endTime);

            $scheduledJobDate = $endTimeObject->copy()->addSecond($job->delay);

            $this->assertCorrectJobScheduleDate($correctScheduleNextStartDate,
                $scheduledJobDate);

            return !is_null($job->delay);
        });
    }

    /**
     * Example:
     *  Start 05:10:00 current morning
     *  End 04:10:00 next morning
     *  So need schedule the post to start on next morning 05:10:00
     *
     * @test
     * @dataProvider twoDayPostsThatShouldDispatchEvent
     */
    public function two_day_post_must_dispatch_start_post_job_for_current_day_start_time(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        Bus::fake([StartPost::class]);

        $post = Post::factory()->create([
            'start_date' => $startDate, 'start_time' => $startTime,
            'end_date'   => $endDate, 'end_time' => $endTime,
            'media_id'   => $this->media->id
        ]);

        // subDay() so post won't expire
        $this->travelTo(Carbon::createFromFormat('Y-m-d H:i:s',
            $endDate.$endTime)->subDay());

        event(new ShouldEndPost($post));

        Bus::assertDispatched(StartPost::class, 1);
        Bus::assertDispatched(function (StartPost $job) use (
            $endTime,
            $startTime
        ) {
            $correctScheduleNextStartDate
                = Carbon::createFromTimeString($startTime);
            $endTimeObject = Carbon::createFromTimeString($endTime);

            $scheduledJobDate = $endTimeObject->copy()->addSecond($job->delay);

            $this->assertCorrectJobScheduleDate($correctScheduleNextStartDate,
                $scheduledJobDate);

            return !is_null($job->delay);
        });
    }

    /**
     * Example:
     *  Start 2021-12-30 15:10:00
     *  End 2022-01-01 16:10:00
     *  Today: 2022-01-01
     *  Need to PostExpire when PostEnd job finishes
     *
     * @test
     * @dataProvider expireDatesWithBothTypesOfTimes
     */
    public function when_expire_date_is_today_must_dispatch_post_expired_event_to_all_displays(
      $startDate,
      $endDate,
      $startTime,
      $endTime
    ) {

      Notification::fake([PostExpired::class]);

      $post = Post::factory()->create([
        'start_date' => $startDate, 'start_time' => $startTime,
            'end_date'   => $endDate, 'end_time' => $endTime,
            'media_id'   => $this->media->id
        ]);

        $displaysWithRaspberry = Display::factory(3)->create();
      $displayWithoutRaspberry = Display::factory(2)->create();
      $randomDisplay = Display::factory()->create();

        foreach (
            [...$displaysWithRaspberry, ...$displayWithoutRaspberry] as $display
        ) {
            $post->displays()->attach($display->id);
        }

        foreach ($displaysWithRaspberry as $display) {
            $raspberry = Raspberry::factory()->make()->toArray();
            $display->raspberry()->create($raspberry);
        }

        foreach ($displayWithoutRaspberry as $display) {
            Raspberry::factory()->create();
        }

        $this->travelTo(Carbon::createFromFormat('Y-m-d H:i:s',
            $endDate.$endTime));

        event(new ShouldEndPost($post));

      Notification::assertTimesSent(count($displaysWithRaspberry) + count($displayWithoutRaspberry),
        PostExpired::class);
        Notification::assertSentTo(
          $post->displays,
          PostExpired::class
        );
        Notification::assertNotSentTo(
          $randomDisplay,
          PostExpired::class
        );
    }

    public function expireDatesWithBothTypesOfTimes(): array
    {
        $test = [];

        $timesData = [
            ...$this->eventTimesOneDay,
            ...$this->eventTimesTwoDay,
        ];

        foreach ($timesData as $eventTime) {
            foreach ($this->expireDates as $date) {
                $startDate = $date['start'];
                $startTime = $eventTime['start'];
                $endDate = $date['end'];
                $endTime = $eventTime['end'];
                $startDateAndTime = "$startDate $startTime";
                $endDateAndTime = "$endDate $endTime";

                $string = "Start: $startDateAndTime | End: $endDateAndTime";

                $test[$string] = [
                    'startDate' => $date['start'],
                    'endDate'   => $date['end'],
                    'startTime' => $eventTime['start'],
                    'endTime'   => $eventTime['end']
                ];
            }
        }
        return $test;
    }
}
