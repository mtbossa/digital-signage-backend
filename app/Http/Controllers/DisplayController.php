<?php

namespace App\Http\Controllers;

use App\Actions\Display\StoreDisplayAction;
use App\Actions\Display\UpdateDisplayAction;
use App\Models\Display;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DisplayController extends Controller
{

  public function index(): Collection
  {
    return Display::all();
  }

  public function store(Request $request, StoreDisplayAction $action): Display
  {
    return $action->handle($request);
  }

  public function show(Display $display): Display
  {
    return $display;
  }

  public function update(Request $request, Display $display, UpdateDisplayAction $action): Display
  {
    return $action->handle($request, $display);
  }

  public function destroy(Display $display): ?bool
  {
    return $display->delete();
  }
}
