<?php

namespace App\Listeners\Post;

use App\Enums\RecurrenceCases;
use App\Events\Post\ShouldEndPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\StartPost;
use App\Models\Post;
use App\Models\Recurrence;
use App\Notifications\Post\PostExpired;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\Constraint\AfterConstraint;

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

    /**
     * @throws InvalidRRule
     * @throws InvalidArgument
     * @throws InvalidWeekday
     */
    private function scheduleRecurrent(): void
    {
        $rule = (new Rule)
            ->setStartDate($this->startTime)
            ->setFreq('DAILY')
            ->setCount(2);
        $constraint = new AfterConstraint($this->startTime);

        // Case Only day, must schedule to next available day, where day = $recurrence->day
        switch ($this->chooseRecurrenceLogic($this->recurrence)) {
            case RecurrenceCases::Day:
            default:
                $rule
                    ->setByMonthDay([$this->recurrence->day]);
                break;
            case RecurrenceCases::IsoWeekday:
                $byDay = $this->mapIsoWeekdayIntoRecurrByDayString();
                $rule->setByDay([$byDay]);
                break;
            case RecurrenceCases::Month:
                $rule->setByMonth([$this->recurrence->month]);
                break;
            // TODO This one expires on last day of the year
            case RecurrenceCases::Year:
                $rule->setEndDate(now()->endOfYear());
                break;
            case RecurrenceCases::IsoWeekdayDay:
                $byDay = $this->mapIsoWeekdayIntoRecurrByDayString();
                $rule->setByDay([$byDay])
                    ->setByMonthDay([$this->recurrence->day]);
                break;
            case RecurrenceCases::IsoWeekdayMonth:
                $byDay = $this->mapIsoWeekdayIntoRecurrByDayString();
                $rule->setByDay([$byDay])
                    ->setByMonth([$this->recurrence->month]);
                break;
        }
        $nextScheduleDate = (new ArrayTransformer)->transform($rule,
            $constraint)
            ->first()
            ->getStart();

        StartPost::dispatch($this->post)
            ->delay($this->endTime->diffInSeconds($nextScheduleDate));
    }

    private function chooseRecurrenceLogic(Recurrence $recurrence
    ): RecurrenceCases {
        $notNullKeys
            = $this->recurrence->getOnlyNotNullRecurrenceValues($recurrence)
            ->keys()->toArray();

        $foundRecurrenceCase
            = $this->possibleRecurrentCases->firstWhere('attributes',
            $notNullKeys);

        return $foundRecurrenceCase['name'];
    }

    /**
     * MO indicates Monday; TU indicates Tuesday; WE indicates Wednesday;
     * TH indicates Thursday; FR indicates Friday; SA indicates Saturday;
     * SU indicates Sunday.
     */
    private function mapIsoWeekdayIntoRecurrByDayString(): string
    {
        $byDayStrings = [
            'MO',
            'TU',
            'WE',
            'TH',
            'FR',
            'SA',
            'SU'
        ];

        // -1 because isoweekday 1 => 'MO' is index 0
        return $byDayStrings[$this->recurrence->isoweekday - 1];
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

    private function scheduleIsoWeekday()
    {
        // Carbon Sunday is 0, not 7
        $isoWeekday = $this->recurrence->isoweekday === 7 ? 0
            : $this->recurrence->isoweekday;

        return $this->startTime->next($isoWeekday)
            ->setTimeFrom($this->startTime);
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
}
