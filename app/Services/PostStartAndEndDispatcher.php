<?php

namespace App\Services;

use App\Events\PostStarted;
use App\Jobs\StartPost;
use App\Models\Post;
use Carbon\Carbon;

class PostStartAndEndDispatcher
{
  private Post $post;

  public function setPost(Post $post): PostStartAndEndDispatcher
  {
    $this->post = $post;
    return $this;
  }

  public function run(): void
  {
    $now = Carbon::now();
    $startDate = Carbon::createFromFormat('Y-m-d', $this->post->start_date);
    $endDate = Carbon::createFromFormat('Y-m-d', $this->post->end_date);
    $startTime = Carbon::createFromTimeString($this->post->start_time);
    $endTime = Carbon::createFromTimeString($this->post->end_time);

    if ($startDate->startOfDay()->isAfter($now)) {
      foreach ($this->post->displays as $display) {
        StartPost::dispatch($this->post);
      }
    } else {
      if ($startTime->isAfter($endTime) || ($startTime->isSameHour($endTime)) && $startTime->isAfter($endTime)) {
        $startOfToday = $now->copy()->startOf('day');
        $endOfToday = $now->copy()->endOf('day');

        $endYesterdayMinutesShowToday = $startOfToday->diffInMinutes($endTime);
        $todayShowEndYesterday = $startOfToday->copy()->addMinute($endYesterdayMinutesShowToday);

        $startTodayMinutesShowToday = $endOfToday->diffInMinutes($startTime);
        $todayShowStartToday = $endOfToday->copy()->subMinute($startTodayMinutesShowToday);

        if ($now->isBetween($todayShowEndYesterday, $todayShowStartToday)) {
          foreach ($this->post->displays as $display) {
            StartPost::dispatch($this->post);
          }
        } else {
          foreach ($this->post->displays as $display) {
            event(new PostStarted($this->post, $display));
          }
        }
      } else {
        if ($this->post->start_date <= now()->format('Y-m-d') && ($now->isAfter($startTime) && $now->isBefore($endTime))) {
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
