<?php

namespace App\Listeners;

use App\Events\PostStarted;
use App\Events\ShouldStartPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\EndPost;
use Carbon\Carbon;

class SchedulePostEnd
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
     * @param  PostStarted  $event
     *
     * @return void
     */
    public function handle(
        ShouldStartPost $event,
    ) {
        $post = $event->post;

        $startTime = Carbon::createFromTimeString($post->start_time);
        $endTime = Carbon::createFromTimeString($post->end_time);

        if (DateAndTimeHelper::isPostFromCurrentDayToNext($startTime,
            $endTime)
        ) {
            $endTime->addDay();
        }

        EndPost::dispatch($post)
            ->delay($startTime->diffInSeconds($endTime));
    }
}
