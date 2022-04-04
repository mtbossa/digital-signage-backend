<?php

namespace App\Http\Controllers;

use App\Actions\Media\StoreMediaAction;
use App\Http\Requests\UpdateMediaRequest;
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
    $request->validate([
      'description' => ['required', 'string', 'max:50'],
      'file' => ['required', 'file', 'max:150000', 'mimes:png,jpg,jpeg,mp4,avi']
    ]);
    return $action->handle($request);
  }

  public function show(Media $media): Media
  {
    return $media;
  }

  public function update(UpdateMediaRequest $request, Media $media): Media
  {
    $media->update($request->validated());
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
