<?php

namespace App\Http\Controllers;

use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class RaspberryController extends Controller
{

  public function index(): Collection
  {
    return Raspberry::all();
  }

  public function store(Request $request): Raspberry
  {
    $raspberry = Raspberry::create($request->except(['display_id']));
    if ($request->display_id) {
      $display = Display::findOrFail($request->display_id);
      $display->raspberry()->save($raspberry);
    }
    return $raspberry;
  }

  public function show(Raspberry $raspberry): Raspberry
  {
    return $raspberry;
  }

  public function update(Request $request, Raspberry $raspberry): Raspberry
  {
    $test = $request->all();

    $raspberry->update($request->all());
    $raspberry->update(['teste' => 'teste']);


    if ($request->display_id) {
      $display = Display::findOrFail($request->display_id);
      $raspberry->display()->associate($display)->save();
    } else {
      if ($raspberry->display_id) {
        $raspberry->display()->disassociate()->save();
      }
    }

    return $raspberry;
  }

  public function destroy(Raspberry $raspberry): ?bool
  {
    return $raspberry->delete();
  }
}
