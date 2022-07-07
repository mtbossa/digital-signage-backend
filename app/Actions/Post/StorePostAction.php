<?php

namespace App\Actions\Post;

use App\Events\PostStarted;
use App\Jobs\StartPost;
use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StorePostAction
{
  public function handle(Request $request): Post
  {
    $media = Media::findOrFail($request->media_id);
    $post = $media->posts()->create($request->except(['media_id']));
    if ($request->has('recurrence_id')) {
      $recurrence = Recurrence::findOrFail($request->recurrence_id);
      $post->recurrence()->associate($recurrence);
      $post->save();

    }
    if ($request->has('displays_ids')) {
      $post->displays()->attach($request->displays_ids);
      $post->load('displays');
    }
    $now = Carbon::now();
    $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $post->start_date.$post->start_time);
    $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $post->end_date.$post->end_time);
    $startTime = Carbon::createFromTimeString($post->start_time);
    $endTime = Carbon::createFromTimeString($post->end_time);

    if ($startDate->isAfter($now)) {
      foreach ($post->displays as $display) {
        StartPost::dispatch($post);
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
          foreach ($post->displays as $display) {
            StartPost::dispatch($post);
          }
        } else {
          foreach ($post->displays as $display) {
            event(new PostStarted($post, $display));
          }
        }
      } else {
        if ($post->start_date <= now()->format('Y-m-d') && ($now->isAfter($startTime) && $now->isBefore($endTime))) {
          foreach ($post->displays as $display) {
            event(new PostStarted($post, $display));
          }
        } else {
          foreach ($post->displays as $display) {
            StartPost::dispatch($post);
          }
        }
      }
    }

    return $post;
  }
}
