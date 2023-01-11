<?php

namespace App\Services\DisplayUpdatesCache;

use Illuminate\Support\Facades\Cache;

class DisplayUpdatesCacheService
{
    public function getCurrentCache(DisplayUpdatesCacheKeysEnum $key, int $display_id): array
    {
        $currentCache = [];

        switch ($key) {
            case DisplayUpdatesCacheKeysEnum::DisplayUpdatesPostCreated:
                $currentCache = Cache::get($this->makeKeyName($key, $display_id), []);
        }

        return $currentCache;
    }

    public function setCurrentCache(DisplayUpdatesCacheKeysEnum $key, int $display_id, mixed $value)
    {
        $currentCache = $this->getCurrentCache($key, $display_id);
        $cached = false;

        switch ($key) {
            case DisplayUpdatesCacheKeysEnum::DisplayUpdatesPostCreated:
                $cached = Cache::put($this->makeKeyName($key, $display_id), [...$currentCache, $value]);
        }

        return $cached;
    }

    public function clearCache(DisplayUpdatesCacheKeysEnum $key, int $display_id)
    {
        $currentCache = $this->getCurrentCache($key, $display_id);
        $forgotten = false;

        switch ($key) {
            case DisplayUpdatesCacheKeysEnum::DisplayUpdatesPostCreated:
                $forgotten = Cache::forget($this->makeKeyName($key, $display_id));
        }

        return $forgotten;
    }

    private function makeKeyName(DisplayUpdatesCacheKeysEnum $key, int $display_id): string
    {
        return $key->name.$display_id;
    }
}
