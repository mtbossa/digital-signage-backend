<?php

namespace App\Http\Controllers;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DisplayPostsSyncController extends Controller
{
  public function __invoke(Request $request, Display $display): \Illuminate\Http\JsonResponse
  {
    if (!$request->has("posts_ids")) {
      return response()->json(["error" => "You must pass current locally stores posts_ids"],
        Response::HTTP_BAD_REQUEST);
    }

    $locallyStoredPostsIds = $request->posts_ids;

    $display->load([
      'posts' => function (BelongsToMany $query) {
        $query->where('expired', false);
      },
    ]);

    $notExpiredPosts = $display->posts;
    $uniqueMediasIds = $notExpiredPosts->pluck('media_id')->unique();
    $neededMedias = Media::query()->with([
      'posts' => function (HasMany $query) use ($notExpiredPosts) {
        $query->whereIn('id', $notExpiredPosts->pluck('id')->toArray());
        $query->select([
          'id', 'start_date', 'end_date', 'start_time', 'end_time', 'expose_time', 'media_id', 'recurrence_id'
        ]);
        $query->with(['recurrence' => fn(BelongsTo $query) => $query->select(['id', 'isoweekday', 'day', 'month'])]);
      }
    ])
      ->select(['id', 'type', 'filename'])
      ->findMany($uniqueMediasIds);

    $deletablePostsIds = Collection::make($locallyStoredPostsIds)->filter(function (string $locallyStoredPostId) use (
      $notExpiredPosts
    ) {
      return !$notExpiredPosts->contains(fn(Post $notExpiredPost
      ) => (int) $locallyStoredPostId === $notExpiredPost->id);
    });

    $deletablePosts = Post::query()->findMany($deletablePostsIds, ['id', 'media_id']);
    $stillNeededMediasIds = Post::query()->select(['media_id', DB::raw("count(*)")])
      ->whereNotIn("id", $deletablePosts->pluck("id"))
      ->whereIn("media_id", $deletablePosts->pluck("media_id")->unique('media_id'))
      ->groupBy('media_id')
      ->get()
      ->pluck('media_id');

    $deletable_medias_ids = $deletablePosts
      ->pluck('media_id')
      ->unique('media_id')
      ->filter(
        fn (int $media_id) => !$stillNeededMediasIds->contains(
          fn (int $stillNeededMediaId) => $stillNeededMediaId === $media_id)
      );

    return response()->json([
      'data' => [
        "available" => $neededMedias, 
        "deletable_posts_ids" => $deletablePostsIds,
        "deletable_medias_ids" => $deletablePostsIds
      ]
    ]);
  }
}
