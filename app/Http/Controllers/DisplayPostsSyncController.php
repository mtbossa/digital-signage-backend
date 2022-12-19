<?php

namespace App\Http\Controllers;

use App\Models\Display;
use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class DisplayPostsSyncController extends Controller
{
  public function __invoke(Request $request, Display $display): \Illuminate\Http\JsonResponse
  {
    $display->load([
      'posts' => function (BelongsToMany $query) {
        $query->where('expired', false);
      },
    ]);

    $posts = $display->posts;
    $uniqueMediasIds = $posts->pluck('media_id')->unique();
    $neededMedias = Media::query()->with([
      'posts' => function (HasMany $query) use ($posts) {
        $query->whereIn('id', $posts->pluck('id')->toArray());
        $query->select(['id', 'start_date', 'end_date', 'start_time', 'end_time', 'expose_time', 'media_id', 'recurrence_id']);
        $query->with(['recurrence' => fn(BelongsTo $query) => $query->select(['id', 'isoweekday', 'day', 'month'])]);
      }
    ])
      ->select(['id', 'type', 'filename'])
      ->findMany($uniqueMediasIds);
    
    
    return response()->json(['data' => $neededMedias]);
  }
}
