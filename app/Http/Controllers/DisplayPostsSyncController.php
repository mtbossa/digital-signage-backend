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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DisplayPostsSyncController extends Controller
{
  public function __invoke(Request $request): \Illuminate\Http\JsonResponse
  {
    if (!$request->has("posts_ids")) {
      return response()->json(["error" => "You must pass current locally stores posts_ids"],
        Response::HTTP_BAD_REQUEST);
    }
    
    $posts_ids = $request->posts_ids;
    $locallyStoredPostsIds = [];
    if (! is_null($posts_ids[0])) {
      // When there aren't any posts stored, the request will come as ?posts_ids=[]
      // which will be transform in an array [null]
      $locallyStoredPostsIds = array_map('intval', $request->posts_ids); 
    }
    
    $display = Auth::user();
    $isDisplay = $display instanceof Display; 
    
    if (!$isDisplay) {
        return response()->json(['message' => 'Not Found!'], 404);
    }

    $display->load([
      'posts' => function (BelongsToMany $query) {
        $query->where('expired', false);
      },
    ]);

    $notExpiredPosts = $display->posts;
    $uniqueMediasIds = $notExpiredPosts->pluck('media_id')->unique();
    $needed_medias = Media::query()->with([
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

    $deletable_posts_ids = Collection::make($locallyStoredPostsIds)->filter(function (string $locallyStoredPostId) use (
      $notExpiredPosts
    ) {
      return !$notExpiredPosts->contains(fn(Post $notExpiredPost
      ) => (int) $locallyStoredPostId === $notExpiredPost->id);
    });

    $deletablePosts = Post::query()->findMany($deletable_posts_ids, ['id', 'media_id']);
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
      "available" => $needed_medias, 
      "deletable_posts_ids" => $deletable_posts_ids,
      "deletable_medias_ids" => $deletable_medias_ids
    ]);
  }
}
