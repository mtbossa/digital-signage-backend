<?php

namespace App\Http\Requests\Raspberry;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRaspberryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'short_name' => ['required', 'string', 'max:30'],
            'mac_address' => ['required', 'mac_address'],
            'serial_number' => ['required', 'string', 'max:50'],
            'observation' => ['nullable', 'string'],
        ];
    }
}
