<?php

namespace App\Http\Controllers;

use App\Http\Resources\RaspberryPostsResource;
use App\Models\Raspberry;
use Carbon\Carbon;
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
        $not_ended = $request->not_ended;

        $display_with_posts = $raspberry->display->query()
            ->with([
                'posts' => function (BelongsToMany $query) use ($not_ended) {
                    $query->when($not_ended, function (Builder $query) {
                        $query->where('end_date', '>=',
                            Carbon::now()->format('Y-m-d'));
                        $query->orWhereNotNull('recurrence_id');
                    });
                    $query->with('media');
                    $query->with('recurrence');
                },
            ])
            ->first();

        return RaspberryPostsResource::collection($display_with_posts->posts);
    }
}
