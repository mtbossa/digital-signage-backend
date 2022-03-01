<?php

namespace App\Http\Controllers;

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
    return Raspberry::create($request->all());
  }

  public function show(Raspberry $raspberry): Raspberry
  {
    return $raspberry;
  }

  public function update(Request $request, Raspberry $raspberry): Raspberry
  {
    $raspberry->update($request->all());
    return $raspberry;
  }

  public function destroy(Raspberry $raspberry): ?bool
  {
    return $raspberry->delete();
  }
}
