<?php

namespace App\Http\Controllers;

use App\Actions\Media\StoreMediaAction;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MediaController extends Controller
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

  public function store(Request $request, StoreMediaAction $action)
  {
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

  public function update(Request $request, Media $media): Media
  {
    $media->update($request->all());
    return $media;
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
