<?php

namespace App\Actions\Post;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\Request;

class StorePostAction
{
  public function handle(Request $request): Post
  {
    $media = Media::findOrFail($request->media_id);
    return $media->posts()->create($request->except(['media_id']));
  }
}
