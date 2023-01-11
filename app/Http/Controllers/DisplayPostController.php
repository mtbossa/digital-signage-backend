<?php

namespace App\Http\Controllers;

use App\Http\Resources\DisplayPostsResource;
use App\Models\Display;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
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
  ): AnonymousResourceCollection|JsonResponse {
        $expired = $request->expired ?? false;
        $fromApp = $request->fromApp ?? false;

        $display->load([
            'posts' => function (BelongsToMany $query) use ($expired) {
                $query->where('expired', $expired);
                $query->with('media');
                $query->with('recurrence');
                $query->orderBy('id');
            },
        ]);

        if ($fromApp && $expired) {
            $mediasIds = $display->posts->groupBy('media.id')->keys()->toArray();
            $postsThatStillDependsOnMedias = $display->posts()
              ->select(['posts.id', 'medias.id as media_id'])
              ->join('medias', 'medias.id', '=', 'posts.media_id')
              ->where(function (
                  Builder $query
              ) use ($mediasIds) {
                  $query->where('expired', false);
                  $query->whereIn('media_id', $mediasIds);
              })
              ->get();

            $test = $postsThatStillDependsOnMedias->keyBy('media.id');

            $correct = $display->posts->map(function (Post $post) use ($test) {
                return [
                    'post_id' => $post->id,
                    'media_id' => $post->media->id,
                    'canDeleteMedia' => ! $test->has($post->media->id),
                ];
            });

            return response()->json(['data' => $correct->toArray()]);
        }

        return DisplayPostsResource::collection($display->posts);
    }
}
