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
            'isoweekday' => ['nullable', 'numeric', 'between:1,7'],
            'day' => ['nullable', 'numeric', 'between:1,31'],
            'month' => ['nullable', 'numeric', 'between:1,12'],
        ];
    }
}
