<?php

namespace App\Jobs;

use App\Events\PostExpired;
use App\Helpers\DateAndTimeHelper;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EndPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Carbon $now;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Post $post)
    {
        $this->now = Carbon::now();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $endDate = Carbon::createFromFormat('Y-m-d', $this->post->end_date);
        $endTime = Carbon::createFromTimeString($this->post->end_time);
        $startTime = Carbon::createFromTimeString($this->post->start_time);

        if ($this->now->isSameUnit('day', $endDate)) {
            foreach ($this->post->displays as $display) {
                event(new PostExpired($this->post, $display));
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
