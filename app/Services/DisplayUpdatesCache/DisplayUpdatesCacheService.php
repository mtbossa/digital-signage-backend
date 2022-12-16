<?php

namespace App\Services\DisplayUpdatesCache;

use Illuminate\Support\Facades\Cache;
class DisplayUpdatesCacheService {
  public function getCurrentCache(DisplayUpdatesCacheKeysEnum $key, int $display_id): array
  {
    $currentCache = [];
    
    switch ($key):
      case DisplayUpdatesCacheKeysEnum::PostCreated:
        $currentCache = Cache::get('DisplayUpdates.PostCreated' . $display_id, []);
    endswitch;
    
    return $currentCache;
  }
  
  public function setCurrentCache(DisplayUpdatesCacheKeysEnum $key, int $display_id, mixed $value) {
    $currentCache = $this->getCurrentCache($key, $display_id);
    $cached = false;

    switch ($key):
      case DisplayUpdatesCacheKeysEnum::PostCreated:
        $cached = Cache::put('DisplayUpdates.PostCreated' . $display_id, [...$currentCache, $value]);
    endswitch;

    return $cached;  
  }
}
