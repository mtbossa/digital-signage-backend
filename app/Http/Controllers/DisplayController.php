<?php

namespace App\Http\Controllers;

use App\Models\Display;
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
    return Display::create($request->all());
  }

  public function show(Display $display): Display
  {
    return $display;
  }

  public function update(Request $request, Display $display): Display
  {
    $display->update($request->all());
    return $display;
  }

  public function destroy(Display $display): ?bool
  {
    return $display->delete();
  }
}
