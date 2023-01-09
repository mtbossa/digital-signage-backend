<?php

namespace App\Http\Controllers;

use App\Http\Resources\DisplayPostsResource;
use App\Models\Raspberry;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RaspberryDisplayPostsController extends Controller
{
  public function __invoke(Request $request)
  {
    $authenticated = Auth::user();
    $isRaspberry = $authenticated instanceof Raspberry;

    if (!$isRaspberry) {
      return response()->json(['message' => 'Not Found!'], 404);
    }

    $hasDisplay = $authenticated->display;

    if (!$hasDisplay) {
      return response()->json(['message' => 'Not Found!'], 404);
    }

    $display = $authenticated->display;

    $display->load([
      'posts' => function (BelongsToMany $query) {
        $query->where('expired', false);
        $query->with('media');
        $query->with('recurrence');
        $query->orderBy('id');
      },
    ]);

    return DisplayPostsResource::collection($display->posts);
  }
}
