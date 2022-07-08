<?php

namespace App\Services;

use App\Events\PostStarted;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\StartPost;
use App\Models\Post;
use Carbon\Carbon;

class PostStartAndEndDispatcherService
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

  public function setPost(Post $post): PostStartAndEndDispatcherService
  {
    $this->post = $post;
    $this->setDatesAndTimes();
    return $this;
  }

  private function setDatesAndTimes()
  {
    $this->startDate = Carbon::createFromFormat('Y-m-d', $this->post->start_date);
    $this->startTime = Carbon::createFromTimeString($this->post->start_time);
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
    if (DateAndTimeHelper::isPostFromCurrentDayToNext($this->startTime, $this->endTime)) {
      [$todayShowEndYesterday, $todayShowStartToday] = $this->handlePostFromOneDayToNext();

      if ($this->isNowBetweenStartAndEndHourAndMinute($todayShowEndYesterday, $todayShowStartToday)) {
        $this->dispatchStartPostJob();
      } else {
        $this->dispatchPostStartedEvent();
      }
      return;
    }

    if ($this->isNowBetweenStartAndEndHourAndMinute($this->startTime, $this->endTime)) {
      $this->dispatchPostStartedEvent();
      return;
    }

    $this->dispatchStartPostJob();
  }

  private function handlePostFromOneDayToNext(): array
  {
    $startOfToday = $this->now->copy()->startOfDay();
    $endOfToday = $this->now->copy()->endOfDay();

    $endYesterdayMinutesShowToday = $startOfToday->diffInMinutes($this->endTime);
    $todayShowEndYesterday = $startOfToday->copy()->addMinute($endYesterdayMinutesShowToday);

    $startTodayMinutesShowToday = $endOfToday->diffInMinutes($this->startTime);
    $todayShowStartToday = $endOfToday->copy()->subMinute($startTodayMinutesShowToday);

    return [$todayShowEndYesterday, $todayShowStartToday];
  }

  private function isNowBetweenStartAndEndHourAndMinute(Carbon $startTime, Carbon $endTime): bool
  {
    return $this->now->isBetween($this->startTime, $this->endTime);
  }

  private function isPostFromCurrentDayToNext(): bool
  {
    return $this->startTime->isAfter($this->endTime);
  }

  private function isTodayBeforeStartDate(): bool
  {
    return $this->startDate->startOfDay()->isAfter($this->now);
  }

  private function dispatchStartPostJob(): void
  {
    foreach ($this->post->displays as $display) {
      StartPost::dispatch($this->post);
    }
  }

  private function dispatchPostStartedEvent(): void
  {
    foreach ($this->post->displays as $display) {
      event(new PostStarted($this->post, $display));
    }
  }
}
