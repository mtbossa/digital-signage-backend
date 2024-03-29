<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
  use PasswordValidationRules;

  /**
   * Validate and create a newly registered user.
   *
   * @param array $input
   * @return User
   */
  public function create(array $input)
  {
    Validator::make($input, [
      'name' => ['required', 'string', 'max:255'],
      'email' => [
        'required',
        'string',
        'email',
        'max:255',
        Rule::unique(User::class),
      ],
      'password' => $this->passwordRules(),
    ])->validate();

    return User::create([
      'name' => $input['name'],
      'email' => $input['email'],
      'password' => Hash::make($input['password']),
      'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
      'is_admin' => $input['is_admin'],
      'store_id' => $input['store_id'],
    ]);
  }
}
