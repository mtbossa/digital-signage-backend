<?php

namespace App\Http\Controllers;

use App\Http\Resources\RaspberryPostsResource;
use App\Models\Display;
use App\Models\Post;
use App\Services\DisplayUpdatesCache\DisplayUpdatesCacheKeysEnum;
use App\Services\DisplayUpdatesCache\DisplayUpdatesCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisplayUpdatesController extends Controller
{
    public function __invoke(Request $request, Display $display, DisplayUpdatesCacheService $display_updates_cache_service): JsonResponse
    {
        $updates = [];
        
        $createdPostsIds = $display_updates_cache_service->getCurrentCache(DisplayUpdatesCacheKeysEnum::PostCreated, $display->id);        
        if (count($createdPostsIds) > 0) {
          $createdPosts = Post::query()->findMany($createdPostsIds);
          $updates['PostCreated'] = RaspberryPostsResource::collection($createdPosts)->resolve();
        }
        
        return response()->json($updates, 200);
    }
}
