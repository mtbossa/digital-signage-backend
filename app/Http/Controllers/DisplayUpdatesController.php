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
        $display_id = $display->id;

        $createdPostsIds = $display_updates_cache_service->getCurrentCache(DisplayUpdatesCacheKeysEnum::DisplayUpdatesPostCreated, $display_id);
        if (count($createdPostsIds) > 0) {
            $createdPosts = Post::query()->findMany($createdPostsIds);
            $updates['PostCreated'] = RaspberryPostsResource::collection($createdPosts)->resolve();

            $display_updates_cache_service->clearCache(DisplayUpdatesCacheKeysEnum::DisplayUpdatesPostCreated, $display_id);
        }

        return response()->json($updates, 200);
    }
}
