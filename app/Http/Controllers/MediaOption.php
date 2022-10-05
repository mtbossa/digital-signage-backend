<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MediaOption extends Controller
{
  public function __invoke(Request $request): Collection
  {
    return Media::all(['id', 'description', 'path', 'type']);
  }
}
