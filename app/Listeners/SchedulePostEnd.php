<?php

namespace App\Listeners;

use App\Events\PostStarted;
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
   * @param  \App\Events\PostStarted  $event
   * @return void
   */
  public function handle(PostStarted $event)
  {
    $endTime = Carbon::createFromTimeString($event->post->end_time);
    $startTime = Carbon::createFromTimeString($event->post->start_time);

    if (DateAndTimeHelper::isPostFromCurrentDayToNext($startTime, $endTime)) {
      $endTime->addDay();
    }

    EndPost::dispatch($event->post)->delay($startTime->diffInSeconds($endTime));
  }
}
