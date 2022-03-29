<?php

namespace App\Http\Controllers;

use App\Actions\Media\StoreMediaAction;
use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MediaController extends Controller
{
  public function index(): Collection
  {
    return Media::all();
  }

  public function store(Request $request, StoreMediaAction $action): Media
  {
    return $action->handle($request);
  }

  public function show(Media $media): Media
  {
    return $media;
  }

  public function update(Request $request, Media $media): Media
  {
    $media->update($request->all());
    return $media;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @return bool
   */
  public function destroy(Media $media)
  {
    return $media->delete();
  }
}
