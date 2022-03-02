<?php

namespace App\Http\Controllers;

use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DisplayController extends Controller
{

  public function index(): Collection
  {
    return Display::all();
  }

  public function store(Request $request): Display
  {
    $display = Display::create($request->except(['raspberry_id']));

    if ($request->raspberry_id) {
      $raspberry = Raspberry::findOrFail($request->raspberry_id);
      $display->raspberry()->save($raspberry);
    }

    return $display;
  }

  public function show(Display $display): Display
  {
    return $display;
  }

  public function update(Request $request, Display $display): Display
  {
    $display->update($request->all());

    if ($request->raspberry_id) {
      $current_raspberry = $display->raspberry;

      if ($current_raspberry->id !== $request->raspberry_id) {
        $current_raspberry->display_id = null;
        $current_raspberry->save();

        $raspberry = Raspberry::findOrFail($request->raspberry_id);
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

  public function destroy(Display $display): ?bool
  {
    return $display->delete();
  }
}
