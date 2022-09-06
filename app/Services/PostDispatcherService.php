<?php

namespace App\Services;

use App\Events\Post\PostMustStart;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\StartPost;
use App\Models\Post;
use Carbon\Carbon;

class PostDispatcherService
{
  private Post $post;
  private Carbon $now;
  private Carbon|null $startDate;
  private Carbon $startTime;
  private Carbon $endTime;

  public function __construct()
  {
    $this->now = Carbon::now();
  }

  public function setPost(Post $post): PostDispatcherService
  {
    $this->post = $post;
    $this->setDatesAndTimes();
    return $this;
  }

  private function setDatesAndTimes()
  {
    if (!$this->post->recurrence) {
      $this->startDate = Carbon::createFromFormat('Y-m-d',
        $this->post->start_date);
    }
    $this->startTime
      = Carbon::createFromTimeString($this->post->start_time);
    $this->endTime = Carbon::createFromTimeString($this->post->end_time);


  }

  public function run(): void
  {
    if ($this->post->recurrence) {
      $this->handleRecurrent();
    } else {
      $this->handleNonRecurrent();
    }
  }

  private function handleRecurrent()
  {
    $recurrence = $this->post->recurrence;
    $recurrenceValues
      = $recurrence->getOnlyNotNullRecurrenceValues($recurrence);

    $allPassed = $recurrenceValues->map(function (
      int $value,
      string $unit
    ) {
      // https://www.php.net/manual/en/datetime.format.php
      switch ($unit) {
        // Default is isoweekday
        default:
          $test = $this->now->isDayOfWeek($value);
          break;
        case 'day':
          // j => Day of the month without leading zeros
          $test = $this->now->isSameDay(Carbon::createFromFormat('j',
            $value));
          break;
        case 'month':
          // 'n' => Numeric representation of a month, without leading zeros
          $test
            = $this->now->isSameMonth(Carbon::createFromFormat('n',
            $value));
          break;
        case 'year':
          // 'Y' 	A full numeric representation of a year, at least 4 digits, with - for years BCE.
          $test = $this->now->isSameYear(Carbon::createFromFormat('Y',
            $value));
          break;
      }
      return $test;
    })
      ->every(fn($value) => $value === true);


    if ($allPassed) {
      $this->checkTimesAndDispatch();
    } else {
      $this->dispatchStartPostJob();
    }
  }


  private function checkTimesAndDispatch(): void
  {
    if (DateAndTimeHelper::isPostFromCurrentDayToNext($this->startTime,
      $this->endTime)
    ) {
      if ($this->isNowBetweenStartAndEndHourAndMinute($this->startTime,
        $this->endTime)
      ) {
        $this->dispatchStartPostJob('time');
      } else {
        $this->dispatchPostMustStartEvent();
      }
    } else {
      if ($this->isNowBetweenStartAndEndHourAndMinute($this->startTime,
        $this->endTime)
      ) {
        $this->dispatchPostMustStartEvent();
      } else {
        $this->dispatchStartPostJob('time');
      }
    }
  }

  private function isNowBetweenStartAndEndHourAndMinute(
    Carbon $startTime,
    Carbon $endTime
  ): bool {
    return $this->now->isBetween($this->startTime, $this->endTime);
  }

  private function dispatchStartPostJob(string $byTimeOrDate = null): void
  {
    if ($this->post->recurrence) {
//      $this->handleRecurrent();
      StartPost::dispatch($this->post);
    } else {
      $this->scheduleNonRecurrent($byTimeOrDate);
    }
  }

  private function scheduleNonRecurrent(string $byTimeOrDate): void
  {

    if ($byTimeOrDate === 'time') {
      $delay = $this->now->diffInSeconds($this->startTime);
    } else {
      $correctStartDateAndTime = $this->startDate->setTimeFrom($this->startTime);
      $delay = $this->now->diffInSeconds($correctStartDateAndTime);
    }

    StartPost::dispatch($this->post)
      ->delay($delay);
  }

  private function dispatchPostMustStartEvent(): void
  {
    event(new PostMustStart($this->post));
  }

  private function handleNonRecurrent()
  {
    // When start date is not today or before, must go to queue
    if ($this->isTodayBeforeStartDate()) {
      $this->dispatchStartPostJob('date');
    } else {
      $this->checkTimesAndDispatch();
    }
  }

  private function isTodayBeforeStartDate(): bool
  {
    return $this->startDate->startOfDay()->isAfter($this->now);
  }
}
