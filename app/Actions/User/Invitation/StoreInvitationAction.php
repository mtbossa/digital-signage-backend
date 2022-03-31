<?php


namespace App\Actions\User\Invitation;

use App\Mail\UserInvitation;
use App\Models\Invitation;
use Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class StoreInvitationAction
{
  use WithFaker;

  public function handle(Request $request): Invitation
  {
    $invited_email = $request->email;
    $invitation = Invitation::create([
      'email' => $invited_email, 'inviter' => Auth::user()->id, 'token' => $this->generateInvitationToken($invited_email)
    ]);
    Mail::to($invited_email)->send(new UserInvitation($invitation));
    return $invitation;
  }

  public function generateInvitationToken(string $email): string
  {
    return substr(md5(rand(0, 9).$email.time()), 0, 32);
  }
}
