<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRecurrenceRequest;
use App\Models\Recurrence;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RecurrenceController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    return Recurrence::all();
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  Request  $request
   * @return Response
   */
  public function store(Request $request)
  {
    return Recurrence::create($request->all());
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show(Recurrence $recurrence)
  {
    return $recurrence;
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  Request  $request
   * @param  int  $id
   * @return Response
   */
  public function update(UpdateRecurrenceRequest $request, Recurrence $recurrence)
  {
    $recurrence->update($request->validated());
    return $recurrence;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy(Recurrence $recurrence)
  {
    return $recurrence->delete();
  }
}
