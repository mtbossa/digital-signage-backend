<?php

namespace App\Listeners\Post;

use App\Events\Post\PostMustStart;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\EndPost;
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

    public function handle(
      PostMustStart $event,
    ): void {
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
