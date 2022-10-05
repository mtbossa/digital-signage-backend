<?php

namespace App\Http\Controllers;

use App\Models\Display;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DisplayOption extends Controller
{
  public function __invoke(Request $request): Collection
  {
    return Display::all(['id', 'name']);
  }
}
