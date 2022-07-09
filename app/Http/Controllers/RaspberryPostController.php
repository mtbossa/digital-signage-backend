<?php

namespace App\Http\Controllers;

use App\Http\Resources\RaspberryPostsResource;
use App\Models\Raspberry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RaspberryPostController extends Controller
{
    /**
     * Gets the current Raspberry Posts through its Display
     */
    public function index(
        Request $request,
        Raspberry $raspberry
    ): AnonymousResourceCollection {
        $showing = $request->showing;

        $display_with_posts = $raspberry->display->query()
            ->with([
                'posts' => function (BelongsToMany $query) use ($showing) {
                    $query->when($showing, function (Builder $query) {
                        $query->where('showing', true);
                    });
                    $query->with('media');
                    $query->with('recurrence');
                },
            ])
            ->first();

        return RaspberryPostsResource::collection($display_with_posts->posts);
    }
}
