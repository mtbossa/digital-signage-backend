<?php


namespace App\Actions\User\Invitation;

use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Mail\UserInvitation;
use App\Models\Invitation;
use Auth;
use Illuminate\Support\Facades\Mail;

class StoreInvitationAction
{
  public function handle(StoreInvitationRequest $request): Invitation
  {
    $invited_email = $request->email;
    $store_id = $request->store_id;
    $is_admin = $request->is_admin;
    $invitation = Invitation::create([
      'email' => $invited_email, 'is_admin' => $is_admin, 'store_id' => $store_id, 'inviter' => Auth::user()->id,
      'token' => Invitation::generateInvitationToken($invited_email)
    ]);
    Mail::to($invited_email)->send(new UserInvitation($invitation));

    return $invitation;
  }
}
