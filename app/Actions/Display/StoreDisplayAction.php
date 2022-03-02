<?php

namespace App\Actions\Display;

use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Http\Request;

class StoreDisplayAction
{
  public function handle(Request $request): Display
  {
    $display = Display::create($request->except(['raspberry_id']));

    if ($request->raspberry_id) {
      $raspberry = Raspberry::findOrFail($request->raspberry_id);
      $display->raspberry()->save($raspberry);
    }

    return $display;
  }
}
