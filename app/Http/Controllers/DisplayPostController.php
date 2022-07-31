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
        $showing = $request->showing;

        $display->load([
            'posts' => function (BelongsToMany $query) use ($showing) {
                $query->when($showing, function (Builder $query) {
                    $query->where('showing', true);
                });
                $query->with('media');
                $query->with('recurrence');
            },
        ]);

        return DisplayPostsResource::collection($display->posts);
    }
}
