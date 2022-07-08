<?php

namespace App\Listeners;

use App\Events\PostStarted;
use App\Events\StartPostJobCompleted;
use App\Services\PostStartAndEndDispatcherService;

class SchedulePostEnd
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(public PostStartAndEndDispatcherService $service
    ) {
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
        StartPostJobCompleted $event,
    ) {
        $this->service->setPost($event->post)->schedulePostEnd();
    }
}
