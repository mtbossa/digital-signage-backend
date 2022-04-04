<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
      'end_time' => ['required', 'date_format:H:i:s', 'after:start_time'],
      'media_id' => ['required', 'integer'],
      'recurrence_id' => ['sometimes', 'prohibits:start_date,end_date'],
    ];
  }
}