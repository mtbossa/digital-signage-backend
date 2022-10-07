<?php

namespace App\Http\Controllers;

use App\Http\Requests\Raspberry\StoreRaspberryRequest;
use App\Http\Requests\Raspberry\UpdateRaspberryRequest;
use App\Mail\InstallationLink;
use App\Models\Display;
use App\Models\Raspberry;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;

class RaspberryController extends Controller
{

  public function index(Request $request): LengthAwarePaginator|JsonResponse
  {
    $search = $request->query("search");
    $searchColumn = $request->query("searchColumn");

    if ($search && !$searchColumn) {
      return response()->json([
        'message' => "searchColumn parameter must be specified when search parameter is not empty."
      ], 400);
    }
    
    $query = Raspberry::query();
    $query->when($search, fn(Builder $query) => $query->where($request->query("searchColumn"), "ilike", "%{$search}%"));

    return $query->paginate($request->size);
  }

  public function store(StoreRaspberryRequest $request): Raspberry
  {
    $raspberry = Raspberry::create($request->validated());

    $new_token = $raspberry->createToken('raspberry_access_token');
    $raspberry->token = $new_token;

    Mail::to($request->user())->queue(new InstallationLink($raspberry));

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
