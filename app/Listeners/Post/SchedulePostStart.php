<?php

namespace App\Listeners\Post;

use App\Events\Post\ShouldEndPost;
use App\Helpers\DateAndTimeHelper;
use App\Interfaces\RecurrenceScheduler;
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
        private RecurrenceScheduler $recurrenceScheduler
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
        $this->recurrenceScheduler->configure($this->recurrence->filteredRecurrence,
            $this->startTime);

        $nextScheduleDate = $this->recurrenceScheduler->scheduleStart();

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
}
