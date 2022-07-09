<?php


namespace Post\Events;

use App\Events\PostExpired;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PostExpiredTest extends TestCase
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
    public function when_end_post_job_completes_must_dispatch_post_expired_when_end_date_is_today(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        Event::fake([PostExpired::class]);

        $this->configureEventsTests($startDate, $endDate, $startTime, $endTime,
            $this->nowDate, $this->displaysAmount);

        // Travels to end time so job is completed (end time is tomorrow)
        $this->travelTo(Carbon::createFromTimeString($endTime)
            ->addDay());

        Event::assertDispatched(PostExpired::class, $this->displaysAmount);
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