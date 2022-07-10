<?php

namespace App\Listeners\Post;

use App\Events\Post\PostExpired;
use App\Events\Post\ShouldEndPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\StartPost;
use Carbon\Carbon;

class SchedulePostStart
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     *
     * @return void
     */
    public function handle(ShouldEndPost $event)
    {
        $post = $event->post;
        $now = Carbon::now();

        $endDate = Carbon::createFromFormat('Y-m-d', $post->end_date);
        $endTime = Carbon::createFromTimeString($post->end_time);
        $startTime = Carbon::createFromTimeString($post->start_time);

        if ($now->isSameUnit('day', $endDate)) {
            foreach ($post->displays as $display) {
                if ($display->raspberry) {
                    event(new PostExpired($post, $display));
                }
            }

            return;
        }

        if (!DateAndTimeHelper::isPostFromCurrentDayToNext($startTime,
            $endTime)
        ) {
            $startTime->addDay();
        }

        StartPost::dispatch($post)
            ->delay($endTime->diffInSeconds($startTime));
    }
}
