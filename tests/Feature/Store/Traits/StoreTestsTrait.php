<?php

namespace Tests\Feature\Store\Traits;

use App\Models\Store;

trait StoreTestsTrait
{
    private Store $store;

    private function _makeStore(array $data = null): Store
    {
        return Store::factory()->make($data);
    }

    private function _createStore(array $data = null): Store
    {
        return Store::factory()->create($data);
    }
}
