<?php

namespace App\Http\Requests\Post;

use App\Models\Display;
use App\Rules\ExposeTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'description' => ['required', 'string', 'max:100'],
      'start_date' => [
        'required_without:recurrence_id', 'required_with:end_date', 'nullable', 'date_format:Y-m-d',
      ],
      'end_date' => [
        'required_without:recurrence_id', 'required_with:start_date', 'nullable', 'date_format:Y-m-d',
        'after_or_equal:start_date',
      ],
      'start_time' => ['required', 'date_format:H:i:s'],
      'end_time' => ['required', 'date_format:H:i:s'],
      'recurrence_id' => ['sometimes', 'prohibits:start_date,end_date'],
      'displays_ids' => ['present', 'nullable', 'array', Rule::in(Display::all()->pluck('id')->toArray())],
      'expose_time' => ['bail', 'nullable', 'numeric', 'min:1000', 'max:3600000', new ExposeTime]
    ];
  }
}
