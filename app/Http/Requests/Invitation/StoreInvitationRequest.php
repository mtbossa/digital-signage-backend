<?php

namespace App\Http\Requests\Invitation;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'email' => ['required', 'email', 'unique:invitations', 'max:255'],
      'store_id' => ['nullable', 'numeric', 'exists:stores,id']
    ];
  }
}
