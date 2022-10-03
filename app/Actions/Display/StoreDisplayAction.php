<?php

namespace App\Actions\Display;

use App\Http\Requests\Display\StoreDisplayRequest;
use App\Models\Display;
use App\Models\Raspberry;
use App\Models\Store;

class StoreDisplayAction
{
    public function handle(StoreDisplayRequest $request): Display
    {
        $display = Display::create($request->except(['raspberry_id']));

        if ($request->raspberry_id) {
            $raspberry = Raspberry::findOrFail($request->raspberry_id);
          $display->raspberry()->save($raspberry);
        }

      if ($request->store_id) {
        $store = Store::findOrFail($request->store_id);
        $display->store()->associate($store);
      }

      return $display;
    }
}
