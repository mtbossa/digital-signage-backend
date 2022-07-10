<?php


namespace Post\Listeners;

use App\Jobs\Post\EndPost;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class SchedulePostEndTest extends TestCase
{
    use RefreshDatabase, PostTestsTrait, AuthUserTrait;

    private int $displaysAmount = 3;
    private string $nowDate = '2022-01-01 15:15:00';

    private array $eventTimesOneDay
        = [
            ['start' => '15:14:00', 'end' => '15:16:00'],
            ['start' => '14:14:00', 'end' => '15:16:00'],
            ['start' => '15:14:00', 'end' => '16:16:00'],
        ];

    private array $eventTimesTwoDay
        = [
            ['start' => '05:10:00', 'end' => '04:10:00'],
            ['start' => '16:14:00', 'end' => '16:13:00'],
            ['start' => '15:14:00', 'end' => '15:13:00'],
        ];

    private array $showDates
        = [
            ['start' => '2021-12-31', 'end' => '2022-01-02'],
            ['start' => '2021-10-05', 'end' => '2022-05-06'],
            ['start' => '2022-01-01', 'end' => '2022-01-02'],
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

    /**
     * @test
     * @dataProvider oneDayPostsThatShouldDispatchEvent
     */
    public function when_one_day_post_end_post_job_must_be_scheduled_to_end_date_on_same_day(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        Bus::fake();

        $this->configureEventsTests($startDate, $endDate, $startTime, $endTime,
            $this->nowDate, $this->displaysAmount);

        Bus::assertDispatched(EndPost::class, 1);
        Bus::assertDispatched(function (EndPost $job) use (
            $endTime,
            $startTime
        ) {
            $correctScheduleEndDate = Carbon::createFromTimeString($endTime);
            $startTimeObject = Carbon::createFromTimeString($startTime);

            $scheduledJobDate = $startTimeObject->copy()
                ->addSecond($job->delay);

            $this->assertCorrectJobScheduleDate($correctScheduleEndDate,
                $scheduledJobDate);

            return !is_null($job->delay);
        });
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

    /**
     * @test
     * @dataProvider twoDayPostsThatShouldDispatchEvent
     */
    public function when_two_day_post_end_post_job_must_be_scheduled_to_end_date_on_next_day(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        $this->withoutExceptionHandling();
        Bus::fake();

        $this->configureEventsTests($startDate, $endDate, $startTime, $endTime,
            $this->nowDate, $this->displaysAmount);

        Bus::assertDispatched(EndPost::class, 1);
        Bus::assertDispatched(function (EndPost $job) use (
            $endTime,
            $startTime
        ) {
            $correctScheduleEndDate = Carbon::createFromTimeString($endTime)
                ->addDay();
            $startTimeObject = Carbon::createFromTimeString($startTime);

            $scheduledJobDate = $startTimeObject->copy()
                ->addSecond($job->delay);

            $this->assertCorrectJobScheduleDate($correctScheduleEndDate,
                $scheduledJobDate);

            return !is_null($job->delay);
        });
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
}
