<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\User\Invitation\StoreInvitationAction;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class InvitationController extends Controller
{

  public function index(): Collection
  {
    return Invitation::all();
  }

  public function store(StoreInvitationRequest $request, StoreInvitationAction $action): Invitation
  {
    return $action->handle($request);
  }

  public function show(Invitation $invitation): Invitation
  {
    return $invitation;
  }

  public function update(Request $request, string $token, CreateNewUser $action): User
  {
    $invitation = Invitation::where('token', $token)->firstOrFail();
    $user = $action->create([...$request->all(), 'email' => $invitation->email, 'store_id' => $invitation->store_id]);
    $invitation->registered_at = Carbon::now()->format('Y-m-d H:i:s');
    $invitation->save();

    return $user;
  }

  public function destroy(Invitation $invitation): bool
  {
    return $invitation->delete();
  }
}
