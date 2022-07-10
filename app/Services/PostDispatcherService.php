<?php

namespace App\Services;

use App\Events\ShouldStartPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\EndPost;
use App\Jobs\StartPost;
use App\Models\Post;
use Carbon\Carbon;

class PostDispatcherService
{
    private Post $post;
    private Carbon $now;
    private Carbon $startDate;
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
        $this->startDate = Carbon::createFromFormat('Y-m-d',
            $this->post->start_date);
        $this->startTime
            = Carbon::createFromTimeString($this->post->start_time);
        $this->endTime = Carbon::createFromTimeString($this->post->end_time);
    }

    public function run(): void
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

    private function dispatchStartPostJob(): void
    {
        StartPost::dispatch($this->post);
    }

    private function isNowBetweenStartAndEndHourAndMinute(
        Carbon $startTime,
        Carbon $endTime
    ): bool {
        return $this->now->isBetween($this->startTime, $this->endTime);
    }

    private function dispatchShouldStartPostEvent(): void
    {
        event(new ShouldStartPost($this->post));
    }

    public function schedulePostEnd(): void
    {
        if (DateAndTimeHelper::isPostFromCurrentDayToNext($this->startTime,
            $this->endTime)
        ) {
            $this->endTime->addDay();
        }

        EndPost::dispatch($this->post)
            ->delay($this->startTime->diffInSeconds($this->endTime));
    }
}
