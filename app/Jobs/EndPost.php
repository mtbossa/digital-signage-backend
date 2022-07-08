<?php

namespace App\Jobs;

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

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(public Post $post)
  {
    //
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $endTime = Carbon::createFromTimeString($this->post->end_time);
    $startTime = Carbon::createFromTimeString($this->post->start_time);

    if (!DateAndTimeHelper::isPostFromCurrentDayToNext($startTime, $endTime)) {
      $startTime->addDay();
    }

    StartPost::dispatch($this->post)->delay($endTime->diffInSeconds($startTime));
  }
}
