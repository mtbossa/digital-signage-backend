<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class StartDate implements InvokableRule
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
    $startDate = Carbon::createFromFormat("Y-m-d", $value);
    if ($startDate->startOfDay()->isBefore(now()->startOfDay())) {
      $fail("The :attribute must not be before today.");
    }
  }
}
