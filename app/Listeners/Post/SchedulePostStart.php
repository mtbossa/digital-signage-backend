<?php

namespace App\Listeners\Post;

use App\Events\Post\ShouldEndPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\StartPost;
use App\Models\Post;
use App\Models\Recurrence;
use App\Notifications\Post\PostExpired;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\Constraint\AfterConstraint;

class SchedulePostStart
{
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

        foreach (
            $this->recurrence->filteredRecurrence as $recurrenceName => $value
        ) {
            switch ($recurrenceName) {
                case 'isoweekday':
                    $rule->setByDay([$this->recurrence->recurrIsoWeekDay]);
                    break;
                case 'day':
                    $rule->setByMonthDay([$this->recurrence->day]);
                    break;
                case 'month':
                    $rule->setByMonth([$this->recurrence->month]);
                    break;
                case 'year':
                    $startDate = $this->recurrence->year
                    > $this->startTime->year
                        ? $this->startTime
                            ->setYear($this->recurrence->year)
                            ->startOfYear()->setTimeFrom($this->startTime)
                        : $this->startTime;

                    $rule->setStartDate($startDate)
                        ->setEndDate(Carbon::createFromFormat('Y',
                            $this->recurrence->year)->endOfYear());
                    break;
            }
        }

        $nextScheduleDate = (new ArrayTransformer)->transform($rule,
            $constraint)
            ->first()
            ->getStart();

        StartPost::dispatch($this->post)
            ->delay($this->endTime->diffInSeconds($nextScheduleDate));
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
}
