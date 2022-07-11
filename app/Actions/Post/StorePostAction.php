<?php

namespace App\Actions\Post;

use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use App\Services\PostDispatcherService;
use Illuminate\Http\Request;

class StorePostAction
{
    public function handle(
        Request $request,
        PostDispatcherService $postDispatcherService
    ): Post {
        $media = Media::findOrFail($request->media_id);
        $post = $media->posts()->create($request->except(['media_id']));

        if ($request->has('recurrence_id')) {
            $recurrence = Recurrence::findOrFail($request->recurrence_id);
            $post->recurrence()->associate($recurrence);
            $post->save();
        }

        if ($request->has('displays_ids')) {
            $post->displays()->attach($request->displays_ids);
            $post->load('displays');
        }

        $postDispatcherService->setPost($post)->run();

        return $post;
    }
}
