<?php

namespace App\Actions\Display;

use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Http\Request;

class UpdateDisplayAction
{
  public function handle(Request $request, Display $display)
  {
    $display->update($request->all());

    if ($request->raspberry_id) {
      $current_raspberry = $display->raspberry;

      if ($current_raspberry->id !== $request->raspberry_id) {
        $raspberry = Raspberry::findOrFail($request->raspberry_id);

        $current_raspberry->display_id = null;
        $current_raspberry->save();

        $display->raspberry()->save($raspberry);
      }
    } else {
      if ($display->raspberry) {
        $current_raspberry = $display->raspberry;
        $current_raspberry->display_id = null;
        $current_raspberry->save();
      }
    }

    return $display;
  }
}
