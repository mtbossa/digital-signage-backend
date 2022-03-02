<?php

namespace App\Actions\Display;

use App\Http\Requests\StoreDisplayRequest;
use App\Models\Display;
use App\Models\Raspberry;

class StoreDisplayAction
{
  public function handle(StoreDisplayRequest $request): Display
  {
    $display = Display::create($request->except(['raspberry_id']));

    if ($request->raspberry_id) {
      $raspberry = Raspberry::findOrFail($request->raspberry_id);
      $display->raspberry()->save($raspberry);
    }

    return $display;
  }
}
