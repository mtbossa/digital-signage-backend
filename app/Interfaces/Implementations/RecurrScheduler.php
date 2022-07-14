<?php

namespace App\Interfaces\Implementations;

use App\Interfaces\RecurrenceScheduler;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeInterface;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\Constraint\AfterConstraint;

class RecurrScheduler implements RecurrenceScheduler
{
    /**
     * @var array $recurrence Recurrence values;
     *  $recurrence = [
     *      'isoweekday'     => (int?)
     *      'day' => (int?)
     *      'month'     => (int?)
     *      'year'     => (int?)
     *    ]
     */
    private array $recurrence;
    private DateTimeInterface|CarbonImmutable $startTime;

    public function __construct(
        private $rule = new Rule(),
    ) {
    }

    public function configure(
        array $recurrence,
        DateTimeInterface|CarbonImmutable $startTime,
    ): void {
        $this->recurrence = $recurrence;
        $this->startTime = $startTime;
    }

    /**
     * @throws InvalidRRule
     * @throws InvalidArgument
     * @throws InvalidWeekday
     */
    public function scheduleStart(): DateTime
    {
        $this->rule
            ->setStartDate($this->startTime)
            ->setFreq('DAILY')
            ->setCount(2);

        $constraint = new AfterConstraint($this->startTime);

        foreach (
            $this->recurrence as $recurrenceName => $value
        ) {
            switch ($recurrenceName) {
                case 'isoweekday':
                    $this->isoWeekdayDayToRecurr();
                    $this->rule->setByDay([$this->recurrence['isoweekday']]);
                    break;
                case 'day':
                    $this->rule->setByMonthDay([$this->recurrence['day']]);
                    break;
                case 'month':
                    $this->rule->setByMonth([$this->recurrence['month']]);
                    break;
                case 'year':
                    $startDate = $this->recurrence['year']
                    > $this->startTime->year
                        ? $this->startTime
                            ->setYear($this->recurrence['year'])
                            ->startOfYear()->setTimeFrom($this->startTime)
                        : $this->startTime;

                    $this->rule->setStartDate($startDate)
                        ->setEndDate(Carbon::createFromFormat('Y',
                            $this->recurrence['year'])->endOfYear());
                    break;
            }
        }

        return (new ArrayTransformer)->transform($this->rule,
            $constraint)
            ->first()
            ->getStart();
    }

    private function isoWeekdayDayToRecurr(): void
    {
        $byDayStrings = [
            'MO',
            'TU',
            'WE',
            'TH',
            'FR',
            'SA',
            'SU'
        ];
        // -1 because isoweekday 1 => 'MO' is index 0
        $this->recurrence['isoweekday']
            = $byDayStrings[$this->recurrence['isoweekday'] - 1];
    }
}
