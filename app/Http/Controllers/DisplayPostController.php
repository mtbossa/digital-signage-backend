<?php

namespace App\Http\Controllers;

use App\Http\Resources\DisplayPostsResource;
use App\Models\Display;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DisplayPostController extends Controller
{
    /**
     * Gets the current Raspberry Posts through its Display
     */
    public function index(
        Request $request,
        Display $display
    ): AnonymousResourceCollection {
        $expired = $request->expired;

        $display->load([
          'posts' => function (BelongsToMany $query) use ($expired) {
            $query->when($expired, function (Builder $query) {
              $query->where('expired', true);
            });
            $query->with('media');
            $query->with('recurrence');
          },
        ]);

        return DisplayPostsResource::collection($display->posts);
    }
}
