<?php

namespace App\Http\Controllers;

use App\Models\Display;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DisplayInstallerDownloadController extends Controller
{
  public function __invoke(Request $request, Display $display): Response|JsonResponse|Application|ResponseFactory
  {
    $authenticated = Auth::user();
    if ($authenticated instanceof Display) {
      if ($authenticated->id !== $display->id) {
        return response()->json(['message' => 'Not Found!'], 404);
      }

      $installScript = Storage::get("app-installation/install-bash-script.txt");
      $replaced = Str::replaceArray('##PLACE##', [$display->id, $request->bearerToken()], $installScript);

      return response($replaced, 200)
        ->header('Content-Type', 'text/plain');
    }

    return response()->json(['message' => 'Not Found!'], 404);
  }
}
