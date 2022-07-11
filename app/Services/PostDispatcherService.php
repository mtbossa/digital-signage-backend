<?php

namespace App\Services;

use App\Events\Post\ShouldStartPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\StartPost;
use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PostDispatcherService
{
    private Post $post;
    private Carbon $now;
    private Carbon|null $startDate;
    private Carbon $startTime;
    private Carbon $endTime;

    public function __construct()
    {
        $this->now = Carbon::now();
    }

    public function setPost(Post $post): PostDispatcherService
    {
        $this->post = $post;
        $this->setDatesAndTimes();
        return $this;
    }

    private function setDatesAndTimes()
    {
        if (!$this->post->recurrence) {
            $this->startDate = Carbon::createFromFormat('Y-m-d',
                $this->post->start_date);
        }
        $this->startTime
            = Carbon::createFromTimeString($this->post->start_time);
        $this->endTime = Carbon::createFromTimeString($this->post->end_time);


    }

    public function run(): void
    {
        if ($this->post->recurrence) {
            $this->handleRecurrent();
        } else {
            $this->handleNonRecurrent();
        }
    }

    private function handleRecurrent()
    {
        $recurrence = $this->post->recurrence;
        $recurrenceValues
            = $this->getOnlyNotNullRecurrenceValues($recurrence);

        $allPassed = $recurrenceValues->map(function (
            int $value,
            string $unit
        ) {
            // https://www.php.net/manual/en/datetime.format.php
            switch ($unit) {
                case 'isoweekday':
                    return $this->now->isDayOfWeek($value);
                case 'day':
                    // j => Day of the month without leading zeros
                    return $this->now->isSameDay(Carbon::createFromFormat('j',
                        $value));
                case 'month':
                    // 'n' => Numeric representation of a month, without leading zeros
                    return $this->now->isSameMonth(Carbon::createFromFormat('n',
                        $value));
                case 'year':
                    // 'Y' 	A full numeric representation of a year, at least 4 digits, with - for years BCE.
                    return $this->now->isSameYear(Carbon::createFromFormat('Y',
                        $value));
            }
        })
            ->every(fn($value) => $value === true);


        if ($allPassed) {
            if (DateAndTimeHelper::isPostFromCurrentDayToNext($this->startTime,
                $this->endTime)
            ) {
                if ($this->isNowBetweenStartAndEndHourAndMinute($this->startTime,
                    $this->endTime)
                ) {
                    $this->dispatchStartPostJob();
                } else {
                    $this->dispatchShouldStartPostEvent();
                }
            } else {
                if ($this->isNowBetweenStartAndEndHourAndMinute($this->startTime,
                    $this->endTime)
                ) {
                    $this->dispatchShouldStartPostEvent();
                } else {
                    $this->dispatchStartPostJob();
                }
            }

            return;
        } else {
            $this->dispatchStartPostJob();
        }
    }

    private function getOnlyNotNullRecurrenceValues(Recurrence $recurrence
    ): Collection {
        $recurrenceAttributes = $recurrence->getAttributes();
        return Collection::make($recurrenceAttributes)
            ->filter(fn($item, $key) => $item
                && ($key === 'isoweekday' || $key === 'day'
                    || $key === 'month'
                    || $key === 'year')
            );
    }

    private function isNowBetweenStartAndEndHourAndMinute(
        Carbon $startTime,
        Carbon $endTime
    ): bool {
        return $this->now->isBetween($this->startTime, $this->endTime);
    }

    private function dispatchStartPostJob(): void
    {
        StartPost::dispatch($this->post);
    }

    private function dispatchShouldStartPostEvent(): void
    {
        event(new ShouldStartPost($this->post));
    }

    private function handleNonRecurrent()
    {
        // When start date is not today or before, must go to queue
        if ($this->isTodayBeforeStartDate()) {
            $this->dispatchStartPostJob();
            return;
        }

        // If startTime is after endTime, means it's a post that must stay visible from current day to next
        if (DateAndTimeHelper::isPostFromCurrentDayToNext($this->startTime,
            $this->endTime)
        ) {
            if ($this->isNowBetweenStartAndEndHourAndMinute($this->startTime,
                $this->endTime)
            ) {
                $this->dispatchStartPostJob();
            } else {
                $this->dispatchShouldStartPostEvent();
            }
            return;
        }

        if ($this->isNowBetweenStartAndEndHourAndMinute($this->startTime,
            $this->endTime)
        ) {
            $this->dispatchShouldStartPostEvent();
            return;
        }

        $this->dispatchStartPostJob();
    }

    private function isTodayBeforeStartDate(): bool
    {
        return $this->startDate->startOfDay()->isAfter($this->now);
    }
}
