<?php

namespace App\Http\Controllers;

use App\Actions\Media\StoreMediaAction;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Models\Media;
use App\Notifications\DisplayPost\PostDeleted;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MediaController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        return Media::query()->paginate($request->size);
    }

    public function store(Request $request, StoreMediaAction $action): Media
    {
        $request->validate([
            'description' => ['required', 'string', 'max:50'],
            'file' => ['required', 'file', 'max:150000', 'mimes:png,jpg,jpeg,mp4,avi'],
        ]);

        return $action->handle($request);
    }

    public function show(Media $media): Media
    {
        return $media;
    }

    public function update(UpdateMediaRequest $request, Media $media): Media
    {
        $media->update($request->validated());

        return $media;
    }

    public function destroy(Media $media)
    {
        $media->load('posts.displays');

        DB::transaction(function () use ($media) {
            $posts = $media->posts;

            foreach ($posts as $post) {
                foreach ($post->displays as $display) {
                    $notification = new PostDeleted($display, $post->id, $post->media->id);

                    if ($display->raspberry) {
                        $display->raspberry->notify($notification);
                    } else {
                        $display->notify($notification);
                    }
                }
            }

            $media->delete();
        });

        return response('', 200);
    }
}
