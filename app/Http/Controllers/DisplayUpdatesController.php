<?php

namespace App\Http\Controllers;

use App\Http\Resources\RaspberryPostsResource;
use App\Models\Display;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DisplayUpdatesController extends Controller
{
    public function __invoke(Request $request, Display $display): JsonResponse
    {
        $updates = [];
        
        $createdPostsIds = Cache::get('DisplayUpdates.PostCreated'.$display->id, []);        
        if (count($createdPostsIds) > 0) {
          $createdPosts = Post::query()->findMany($createdPostsIds);
          $updates['PostCreated'] = RaspberryPostsResource::collection($createdPosts)->resolve();
        }
        
        return response()->json($updates, 200);
    }
}
