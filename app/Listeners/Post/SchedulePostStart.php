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
use Illuminate\Support\Collection;

class SchedulePostStart
{
    private Collection $possibleRecurrentCases;
    private Post $post;
    private Recurrence|null $recurrence;
    private Carbon $startTime;
    private Carbon $endTime;
    private Carbon $startTimeCopy;

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
        $this->configureStartTimeCopy();

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
        $this->startTime
            = Carbon::createFromTimeString($this->post->start_time);
        $this->endTime = Carbon::createFromTimeString($this->post->end_time);
    }

    private function configureStartTimeCopy()
    {
        $this->startTimeCopy = $this->startTime->copy();

        if (DateAndTimeHelper::isPostFromCurrentDayToNext($this->startTime,
            $this->endTime)
        ) {
            $this->startTimeCopy->subDay();
        }
    }

    private function scheduleRecurrent()
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

    private function scheduleDay(): Carbon
    {
        // Tries to add one month without overflowing to the next one
        $test1 = $this->startTimeCopy->copy()->addMonthWithNoOverflow();

        // In case it's not the same day 30 days after, means it's a month with less days
        // so need to go to next month
        if ($test1->day !== $this->recurrence->day) {
            $this->startTimeCopy->addMonthsWithNoOverflow(2);
        } else {
            $this->startTimeCopy->addMonthWithNoOverflow();
        }

        return $this->startTimeCopy;
    }

    private function scheduleIsoWeekday()
    {
        $isoWeekday = $this->recurrence->isoweekday === 7 ? 0
            : $this->recurrence->isoweekday;

        $this->startTimeCopy->next($isoWeekday)
            ->setTimeFrom($this->startTime);

        return $this->startTimeCopy;
    }

    private function scheduleNonRecurrent(): void
    {
        $endDate = Carbon::createFromFormat('Y-m-d', $this->post->end_date);
        $endTime = Carbon::createFromTimeString($this->post->end_time);
        $startTime = Carbon::createFromTimeString($this->post->start_time);

        if ($this->now->isSameUnit('day', $endDate)) {
            foreach ($this->post->displays as $display) {
                if ($display->raspberry) {
                    $display->raspberry->notify(new PostExpired($this->post,
                        $display));
                }
            }

            return;
        }

        if (!DateAndTimeHelper::isPostFromCurrentDayToNext($startTime,
            $endTime)
        ) {
            $startTime->addDay();
        }

        StartPost::dispatch($this->post)
            ->delay($endTime->diffInSeconds($startTime));
    }
}
