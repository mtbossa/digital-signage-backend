<?php

namespace App\Http\Controllers;

use App\Http\Resources\DisplayPostsResource;
use App\Models\Display;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;

class DisplayPostController extends Controller
{
  public function index(Request $request, int $display_id)
  {
    $not_ended = $request->not_ended;

    $display_with_posts = Display::query()
      ->with([
        'posts' => function (BelongsToMany $query) use ($not_ended) {
          $query->when($not_ended, function (Builder $query) {
            $query->where('end_date', '>=', Carbon::now()->format('Y-m-d'));
            $query->orWhereNotNull('recurrence_id');
          });
          $query->with('media');
          $query->with('recurrence');
        },
      ])
      ->find($display_id);

    $test = Display::query()
      ->with([
        'posts' => function (BelongsToMany $query) use ($not_ended) {
          $query->when($not_ended, function (Builder $query) {
            $query->where('end_date', '>=', Carbon::now()->format('Y-m-d'));
            $query->orWhereNotNull('recurrence_id');
          })->toSql();
          $query->with('media');
          $query->with('recurrence');
        },
      ])
      ->find($display_id);

    return DisplayPostsResource::collection($display_with_posts->posts);
  }
}
