<?php

namespace App\Http\Requests\Recurrence;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurrenceRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'description' => ['required', 'string', 'max:50'],
      'isoweekday' => ['nullable', 'numeric', 'between:1,7', 'required_without_all:day,month,year'],
      'day' => ['nullable', 'numeric', 'between:1,31', 'required_without_all:isoweekday,month,year'],
      'month' => ['nullable', 'numeric', 'between:1,12', 'required_without_all:isoweekday,day,year'],
      'year' => ['nullable', 'numeric', 'between:2000,2500', 'required_without_all:isoweekday,day,month'],
    ];
  }
}
