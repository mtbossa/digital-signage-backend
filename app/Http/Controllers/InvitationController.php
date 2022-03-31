<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\User\Invitation\StoreInvitationAction;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvitationController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    //
  }

  public function store(Request $request, StoreInvitationAction $action): Invitation
  {
    $request->validate(['email' => ['required', 'email', 'unique:invitations', 'max:255']]);
    return $action->handle($request);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {
    //
  }

  public function update(Request $request, string $token, CreateNewUser $action): User
  {
    $invitation = Invitation::where('token', $token)->firstOrFail();
    $user = $action->create([...$request->all(), 'email' => $invitation->email]);
    $invitation->registered_at = Carbon::now()->format('Y-m-d H:i:s');
    $invitation->save();

    return $user;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {
    //
  }
}
