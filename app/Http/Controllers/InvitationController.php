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
use Illuminate\Support\Facades\Auth;

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

  public function show(Request $request, string $token): Invitation
  {
    $is_guest = Auth::guest();
    $invitation = Invitation::query()
      ->where('token', $token)
      ->when($is_guest, function ($query, $is_guest) {
        $query->where('registered_at', null);
      })
      ->firstOrFail();
    return $invitation;
  }

  public function update(Request $request, string $token, CreateNewUser $action): User
  {
    $invitation = Invitation::query()
      ->where('token', $token)
      ->where('registered_at', null)
      ->firstOrFail();
    $user = $action->create([
      ...$request->all(), 'email' => $invitation->email, 'store_id' => $invitation->store_id,
      'is_admin' => $invitation->is_admin
    ]);
    $invitation->registered_at = Carbon::now()->format('Y-m-d H:i:s');
    $invitation->save();

    return $user;
  }

  public function destroy(Invitation $invitation): bool
  {
    return $invitation->delete();
  }
}
