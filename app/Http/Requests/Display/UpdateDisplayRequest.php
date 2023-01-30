<?php

namespace App\Http\Requests\Display;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDisplayRequest extends FormRequest
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
      'name' => 'required|max:100',
      'size' => 'required|numeric|min:1|max:1000',
      'width' => 'required|numeric|min:1|max:20000',
      'height' => 'required|numeric|min:1|max:20000',
      'observation' => 'nullable|string',
      'raspberry_id' => 'nullable|numeric'
    ];
  }
}
