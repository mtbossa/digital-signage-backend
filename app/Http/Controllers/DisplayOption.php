<?php

namespace App\Http\Controllers;

use App\Models\Display;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DisplayOption extends Controller
{
  public function __invoke(Request $request): Collection
  {
    $columns = ['id', 'name'];
    if ($request->has('whereDoesntHaveRaspberry')) {
      return Display::query()->whereDoesntHave("raspberry")->get($columns);
    }
    return Display::all($columns);
  }
}
