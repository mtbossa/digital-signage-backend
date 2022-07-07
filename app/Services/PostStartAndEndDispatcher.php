<?php

namespace App\Services;

use App\Events\PostStarted;
use App\Jobs\StartPost;
use App\Models\Post;
use Carbon\Carbon;

class PostStartAndEndDispatcher
{
  private Post $post;
  private Carbon $now;
  private Carbon $startDate;
  private Carbon $endDate;
  private Carbon $startTime;
  private Carbon $endTime;

  public function __construct()
  {
    $this->now = Carbon::now();
  }

  public function setPost(Post $post): PostStartAndEndDispatcher
  {
    $this->post = $post;
    $this->setDatesAndTimes();
    return $this;
  }

  private function setDatesAndTimes()
  {
    $this->startDate = Carbon::createFromFormat('Y-m-d', $this->post->start_date);
    $this->endDate = Carbon::createFromFormat('Y-m-d', $this->post->end_date);
    $this->startTime = Carbon::createFromTimeString($this->post->start_time);
    $this->endTime = Carbon::createFromTimeString($this->post->end_time);
  }

  public function run(): void
  {
    if ($this->startDate->startOfDay()->isAfter($this->now)) {
      foreach ($this->post->displays as $display) {
        StartPost::dispatch($this->post);
      }
    } else {
      if ($this->startTime->isAfter($this->endTime) || ($this->startTime->isSameHour($this->endTime)) && $this->startTime->isAfter($this->endTime)) {
        $startOfToday = $this->now->copy()->startOf('day');
        $endOfToday = $this->now->copy()->endOf('day');

        $endYesterdayMinutesShowToday = $startOfToday->diffInMinutes($this->endTime);
        $todayShowEndYesterday = $startOfToday->copy()->addMinute($endYesterdayMinutesShowToday);

        $startTodayMinutesShowToday = $endOfToday->diffInMinutes($this->startTime);
        $todayShowStartToday = $endOfToday->copy()->subMinute($startTodayMinutesShowToday);

        if ($this->now->isBetween($todayShowEndYesterday, $todayShowStartToday)) {
          foreach ($this->post->displays as $display) {
            StartPost::dispatch($this->post);
          }
        } else {
          foreach ($this->post->displays as $display) {
            event(new PostStarted($this->post, $display));
          }
        }
      } else {
        if ($this->post->start_date <= now()->format('Y-m-d') && ($this->now->isAfter($this->startTime) && $this->now->isBefore($this->endTime))) {
          foreach ($this->post->displays as $display) {
            event(new PostStarted($this->post, $display));
          }
        } else {
          foreach ($this->post->displays as $display) {
            StartPost::dispatch($this->post);
          }
        }
      }
    }
  }
}
