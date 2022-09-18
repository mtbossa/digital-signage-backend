<?php

namespace App\Http\Controllers;

use App\Http\Requests\Raspberry\StoreRaspberryRequest;
use App\Http\Requests\Raspberry\UpdateRaspberryRequest;
use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Database\Eloquent\Collection;

class RaspberryController extends Controller
{

  public function index(): Collection
  {
    return Raspberry::all();
  }

  public function store(StoreRaspberryRequest $request): Raspberry
  {
    $raspberry = Raspberry::create($request->safe()->except(['display_id']));
    if ($request->display_id) {
      $display = Display::findOrFail($request->display_id);
      $raspberry->display()->associate($display)->save();
    }

    return $raspberry;
  }

  public function show(Raspberry $raspberry): Raspberry
  {
    return $raspberry;
  }

  public function update(UpdateRaspberryRequest $request, Raspberry $raspberry): Raspberry
  {
    $raspberry->update($request->validated());

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
