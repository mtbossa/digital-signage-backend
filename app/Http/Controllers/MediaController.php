<?php

namespace App\Http\Controllers;

use App\Actions\Media\StoreMediaAction;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MediaController extends Controller
{
  public function index(Request $request): LengthAwarePaginator
  {
    return Media::query()->paginate($request->size);
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

  public function destroy(Media $media)
  {
    return $media->delete();
  }
}
