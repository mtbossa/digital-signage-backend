<?php

namespace App\Listeners\Post;

use App\Events\Post\ShouldEndPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\StartPost;
use App\Models\Post;
use App\Notifications\Post\PostExpired;
use Carbon\Carbon;

class SchedulePostStart
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private Carbon $now, private Post $post)
    {
        $this->now = Carbon::now();
    }

    public function handle(ShouldEndPost $event): void
    {
        $this->post = $event->post;

        $this->scheduleNonRecurrent();
    }

    private function scheduleNonRecurrent()
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
