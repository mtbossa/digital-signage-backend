<?php

namespace App\Helpers;


use Carbon\Carbon;

class DateAndTimeHelper
{
  public static function isPostFromCurrentDayToNext(Carbon $startTime, Carbon $endTime): bool
  {
    return $startTime->isAfter($endTime);
  }
}
