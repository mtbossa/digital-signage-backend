<?php

namespace App\Http\Controllers;

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

    $posts_with_media = Display::query()
      ->find($display_id)
      ->with([
        'posts' => function (BelongsToMany $query) use ($not_ended) {
          $query->when($not_ended, function (Builder $query) {
            $query->where('end_date', '>=', Carbon::now()->format('Y-m-d'));
          });
          $query->with('media');
        }
      ])
      ->get();

    return $posts_with_media;
  }
}
