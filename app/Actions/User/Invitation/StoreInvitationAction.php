<?php


namespace App\Actions\User\Invitation;

use App\Models\Invitation;
use Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;

class StoreInvitationAction
{
  use WithFaker;

  public function handle(Request $request): Invitation
  {
    $email = $request->email;
    return Invitation::create([
      'email' => $email, 'inviter' => Auth::user()->id, 'token' => $this->generateInvitationToken($email)
    ]);
  }

  public function generateInvitationToken(string $email): string
  {
    return substr(md5(rand(0, 9).$email.time()), 0, 32);
  }
}
