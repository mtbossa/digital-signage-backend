<?php

namespace Post\ScheduleWhenCreating;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Post\Enums\PostShouldDo;
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
    private array $eventTimes
        = [
            ['start' => '15:14:00', 'end' => '15:16:00'],
            ['start' => '14:14:00', 'end' => '15:16:00'],
            ['start' => '15:14:00', 'end' => '16:16:00'],
            ['start' => '05:10:00', 'end' => '04:10:00'],
            // start one day, finishes tomorrow
            ['start' => '16:14:00', 'end' => '16:13:00'],
            // start one day, finishes tomorrow
            ['start' => '15:14:00', 'end' => '15:13:00'],
            // start one day, finishes tomorrow
        ];
    // Between these times, must always place in the queue
    private array $queueTimes
        = [
            ['start' => '15:16:00', 'end' => '15:17:00'],
            ['start' => '15:13:00', 'end' => '15:14:00'],
            ['start' => '16:10:00', 'end' => '15:14:00'],
        ];

    // Between these dates, could either queue or dispatch event
    // because the time will choose what must be done
    private array $showDates
        = [
            ['start' => '2021-12-31', 'end' => '2022-01-02'],
            ['start' => '2021-12-31', 'end' => '2022-01-01'],
            ['start' => '2021-10-05', 'end' => '2022-05-06'],
            ['start' => '2022-01-01', 'end' => '2022-01-02'],
            ['start' => '2022-01-01', 'end' => '2022-01-01'],
        ];

    // Always will queue when start date is after today
    private array $queueDates
        = [
            ['start' => '2022-01-02', 'end' => '2022-01-03'],
            ['start' => '2022-05-20', 'end' => '2022-11-20'],
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
     * @dataProvider dateAndTimeThatShouldDispatchEvent
     */
    public function when_creating_post_should_dispatch_event(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        $this->showPostAssertion(
            $startDate,
            $endDate,
            $startTime,
            $endTime,
            $this->nowDate,
            $this->displaysAmount,
            PostShouldDo::Event
        );
    }

    // Dispatching event always depend on time
    public function dateAndTimeThatShouldDispatchEvent(): array
    {
        $test = [];
        foreach ($this->eventTimes as $eventTime) {
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
     * @dataProvider shouldQueueByTimeOnly
     */
    public function when_creating_post_should_queue_time_only(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        $this->showPostAssertion(
            $startDate,
            $endDate,
            $startTime,
            $endTime,
            $this->nowDate,
            $this->displaysAmount,
            PostShouldDo::Queue
        );
    }

    public function shouldQueueByTimeOnly(): array
    {
        $test = [];
        foreach ($this->queueTimes as $queueTime) {
            foreach ($this->showDates as $showDate) {
                $startDate = $showDate['start'];
                $startTime = $queueTime['start'];
                $endDate = $showDate['end'];
                $endTime = $queueTime['end'];
                $startDateAndTime = "$startDate $startTime";
                $endDateAndTime = "$endDate $endTime";

                $string = "Start: $startDateAndTime | End: $endDateAndTime";

                $test[$string] = [
                    'startDate' => $showDate['start'],
                    'endDate'   => $showDate['end'],
                    'startTime' => $queueTime['start'],
                    'endTime'   => $queueTime['end']
                ];
            }
        }
        return $test;
    }

    /**
     * @test
     * @dataProvider shouldQueueByDateOnly
     */
    public function when_creating_post_should_queue_date_only(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        $this->showPostAssertion(
            $startDate,
            $endDate,
            $startTime,
            $endTime,
            $this->nowDate,
            $this->displaysAmount,
            PostShouldDo::Queue
        );
    }

    public function shouldQueueByDateOnly(): array
    {
        $test = [];
        foreach ($this->eventTimes as $eventTime) {
            foreach ($this->queueDates as $queueDate) {
                $startDate = $queueDate['start'];
                $startTime = $eventTime['start'];
                $endDate = $queueDate['end'];
                $endTime = $eventTime['end'];
                $startDateAndTime = "$startDate $startTime";
                $endDateAndTime = "$endDate $endTime";

                $string = "Start: $startDateAndTime | End: $endDateAndTime";

                $test[$string] = [
                    'startDate' => $queueDate['start'],
                    'endDate'   => $queueDate['end'],
                    'startTime' => $eventTime['start'],
                    'endTime'   => $eventTime['end']
                ];
            }
        }
        return $test;
    }

    /**
     * @test
     * @dataProvider shouldQueueByDateAndTime
     */
    public function when_creating_post_should_queue_date_and_time(
        $startDate,
        $endDate,
        $startTime,
        $endTime
    ) {
        $this->showPostAssertion(
            $startDate,
            $endDate,
            $startTime,
            $endTime,
            $this->nowDate,
            $this->displaysAmount,
            PostShouldDo::Queue
        );
    }

    public function shouldQueueByDateAndTime(): array
    {
        $test = [];
        foreach ($this->queueTimes as $eventTime) {
            foreach ($this->queueDates as $queueDate) {
                $startDate = $queueDate['start'];
                $startTime = $eventTime['start'];
                $endDate = $queueDate['end'];
                $endTime = $eventTime['end'];
                $startDateAndTime = "$startDate $startTime";
                $endDateAndTime = "$endDate $endTime";

                $string = "Start: $startDateAndTime | End: $endDateAndTime";

                $test[$string] = [
                    'startDate' => $queueDate['start'],
                    'endDate'   => $queueDate['end'],
                    'startTime' => $eventTime['start'],
                    'endTime'   => $eventTime['end']
                ];
            }
        }
        return $test;
    }


}
