<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class EndTime implements InvokableRule, DataAwareRule
{
  /**
   * Run the validation rule.
   *
   * @param  string  $attribute
   * @param  mixed  $value
   * @param  Closure(string): PotentiallyTranslatedString  $fail
   * @return void
   */
  public function __invoke($attribute, $value, $fail)
  {
    // If post is recurrent, no need to check, because post won't expire
    if (array_key_exists('recurrence_id', $this->data)) {
      return;
    }

    if (!array_key_exists('end_date', $this->data)) {
      return;
    }

    $endDateString = $this->data["end_date"];

    if (is_null($endDateString)) {
      return;
    }

    $endDate = Carbon::createFromFormat("Y-m-d", $this->data["end_date"]);

    // If $endDate is after today date there's no need to check
    if ($endDate->startOfDay()->isAfter(now()->startOfDay())) {
      return;
    }

    $endDate->setTimeFromTimeString($this->data["end_time"]);

    if ($endDate->isBefore(now())) {
      $fail('The :attribute must be after now time.');
    }
  }

  public function setData($data): ExposeTime|static
  {
    $this->data = $data;

    return $this;
  }
}
