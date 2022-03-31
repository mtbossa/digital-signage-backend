<?php

namespace App\Http\Controllers;

use App\Actions\User\Invitation\StoreInvitationAction;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

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

  /**
   * Store a newly created resource in storage.
   *
   * @param  Request  $request
   * @return Response
   */
  public function store(Request $request, StoreInvitationAction $action)
  {
    $request->validate(['email' => ['required', 'email', 'unique:invitations']]);
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

  /**
   * Update the specified resource in storage.
   *
   * @param  Request  $request
   * @param  int  $id
   * @return Response
   */
  public function update(Request $request, string $token)
  {
    $invitation = Invitation::where('token', $token)->firstOrFail();
    $invitation->registered_at = Carbon::now()->format('Y-m-d H:i:s');
    $invitation->save();

    return User::create(['email' => $invitation->email, 'name' => $request->name, 'password' => Hash::make($request->password)]);
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
