<?php

namespace App\Http\Controllers;

use App\Actions\Display\StoreDisplayAction;
use App\Actions\Display\UpdateDisplayAction;
use App\Http\Requests\Display\StoreDisplayRequest;
use App\Models\Display;
use App\Models\Media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DisplayController extends Controller
{

  public function index(Request $request): LengthAwarePaginator
  {
    return Display::query()->paginate($request->size);
  }

  public function store(StoreDisplayRequest $request, StoreDisplayAction $action): Display
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
