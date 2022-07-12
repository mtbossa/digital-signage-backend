<?php

namespace App\Listeners\Post;

use App\Enums\RecurrenceCases;
use App\Events\Post\ShouldEndPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\StartPost;
use App\Models\Post;
use App\Models\Recurrence;
use App\Notifications\Post\PostExpired;
use App\Services\PostDispatcherService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class SchedulePostStart
{
    private Collection $possibleRecurrentCases;
    private Post $post;
    private Recurrence|null $recurrence;
    private CarbonImmutable|DateTimeImmutable $startTime;
    private CarbonImmutable $endTime;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private Carbon $now,
        private PostDispatcherService $postDispatcherService
    ) {
        $this->now = Carbon::now();
        $this->possibleRecurrentCases
            = new Collection($this->getRecurrenceCases());
    }

    private function getRecurrenceCases(): array
    {
        return [
            [
                'name'       => RecurrenceCases::IsoWeekday,
                'attributes' => ['isoweekday']
            ],
            [
                'name'       => RecurrenceCases::IsoWeekdayDay,
                'attributes' => ['isoweekday', 'day'],
            ],
            [
                'name'       => RecurrenceCases::IsoWeekdayMonth,
                'attributes' => ['isoweekday', 'month'],
            ],
            [
                'name'       => RecurrenceCases::IsoWeekdayYear,
                'attributes' => ['isoweekday', 'year'],
            ],
            [
                'name'       => RecurrenceCases::IsoWeekdayDayMonth,
                'attributes' => ['isoweekday', 'day', 'month'],
            ],
            [
                'name'       => RecurrenceCases::IsoWeekdayDayYear,
                'attributes' => ['isoweekday', 'day', 'year'],
            ],
            [
                'name'       => RecurrenceCases::IsoWeekdayDayMonthYear,
                'attributes' => ['isoweekday', 'day', 'month', 'year'],
            ],
            [
                'name'       => RecurrenceCases::Day,
                'attributes' => ['day'],
            ],
            [
                'name'       => RecurrenceCases::DayMonth,
                'attributes' => ['day', 'month'],
            ],
            [
                'name'       => RecurrenceCases::DayYear,
                'attributes' => ['day', 'year'],
            ],
            [
                'name'       => RecurrenceCases::DayMonthYear,
                'attributes' => ['day', 'month', 'year'],
            ],
            [
                'name'       => RecurrenceCases::Month,
                'attributes' => ['month'],
            ],
            [
                'name'       => RecurrenceCases::MonthYear,
                'attributes' => ['month', 'year'],
            ],
            [
                'name'       => RecurrenceCases::Year,
                'attributes' => ['year'],
            ],
        ];
    }

    public function handle(ShouldEndPost $event): void
    {
        $this->setValues($event);

        if ($this->recurrence) {
            $this->scheduleRecurrent();
        } else {
            $this->scheduleNonRecurrent();
        }
    }

    private function setValues(ShouldEndPost $event)
    {
        $this->post = $event->post;
        $this->recurrence = $this->post->recurrence;
        $this->createTimes();
    }

    private function createTimes(): void
    {
        // End time will always be today's date, that's why we can create
        // using ::createFromTimeString, since it sets the date to today's date
        $this->endTime
            = CarbonImmutable::createFromTimeString($this->post->end_time);

        if (DateAndTimeHelper::isPostFromCurrentDayToNext($this->post->start_time,
            $this->endTime)
        ) {
            $this->startTime
                = CarbonImmutable::parse("yesterday {$this->post->start_time}");
        } else {
            $this->startTime
                = CarbonImmutable::createFromTimeString($this->post->start_time);
        }
    }

    private function scheduleRecurrent(): void
    {
        // Case Only day, must schedule to next available day, where day = $recurrence->day
        switch ($this->chooseRecurrenceLogic($this->recurrence)) {
            case RecurrenceCases::Day:
            default:
                $nextScheduleDate = $this->scheduleDay();
                break;
            case RecurrenceCases::IsoWeekday:
                $nextScheduleDate = $this->scheduleIsoWeekday();
                break;
        }

        StartPost::dispatch($this->post)
            ->delay($this->endTime->diffInSeconds($nextScheduleDate));
    }

    private function chooseRecurrenceLogic(Recurrence $recurrence
    ): RecurrenceCases {
        $notNullKeys
            = $this->postDispatcherService->getOnlyNotNullRecurrenceValues($recurrence)
            ->keys()->toArray();

        $foundRecurrenceCase
            = $this->possibleRecurrentCases->firstWhere('attributes',
            $notNullKeys);

        return $foundRecurrenceCase['name'];
    }

    private function scheduleDay(): CarbonImmutable
    {
        // In case it's not the same day 30 days after, means it's a month with fewer days
        // so need to go to next month
        if ($this->startTime->addMonthWithNoOverflow()->day
            !== $this->recurrence->day
        ) {
            return $this->startTime->addMonthsWithNoOverflow(2);
        } else {
            return $this->startTime->addMonthWithNoOverflow();
        }

    }

    private function scheduleIsoWeekday()
    {
        // Carbon Sunday is 0, not 7
        $isoWeekday = $this->recurrence->isoweekday === 7 ? 0
            : $this->recurrence->isoweekday;

        return $this->startTime->next($isoWeekday)
            ->setTimeFrom($this->startTime);
    }

    private function scheduleNonRecurrent(): void
    {
        $endDate = Carbon::createFromFormat('Y-m-d', $this->post->end_date);

        if ($this->now->isSameUnit('day', $endDate)) {
            foreach ($this->post->displays as $display) {
                if ($display->raspberry) {
                    $display->raspberry->notify(new PostExpired($this->post,
                        $display));
                }
            }

            return;
        }

        if (!DateAndTimeHelper::isPostFromCurrentDayToNext($this->startTime,
            $this->endTime)
        ) {
            StartPost::dispatch($this->post)
                ->delay($this->endTime->diffInSeconds($this->startTime->addDay()));
        } else {
            StartPost::dispatch($this->post)
                ->delay($this->endTime->diffInSeconds($this->startTime));
        }


    }
}
