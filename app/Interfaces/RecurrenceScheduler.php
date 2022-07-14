<?php

namespace App\Interfaces;

use Carbon\CarbonImmutable;
use DateTimeInterface;

interface RecurrenceScheduler
{
    public function scheduleStart(): DateTimeInterface;

    /**
     * @param  array  $recurrence  Recurrence values;
     *  $recurrence = [
     *      'isoweekday'     => (int?)
     *      'day' => (int?)
     *      'month'     => (int?)
     *      'year'     => (int?)
     *    ]
     * @param  CarbonImmutable|DateTimeInterface  $startTime
     *
     * @return void
     */
    public function configure(
        array $recurrence,
        CarbonImmutable|DateTimeInterface $startTime,
    ): void;
}