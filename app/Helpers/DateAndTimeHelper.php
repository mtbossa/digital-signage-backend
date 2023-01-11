<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

class DateAndTimeHelper
{
    public static function isPostFromCurrentDayToNext(
        Carbon|CarbonImmutable|string $startTime,
        Carbon|CarbonImmutable|string $endTime
    ): bool {
        if (is_string($startTime)) {
            $startTime = Carbon::createFromTimeString($startTime);
        }
        if (is_string($endTime)) {
            $endTime = Carbon::createFromTimeString($endTime);
        }

        return $startTime->isAfter($endTime);
    }
}
