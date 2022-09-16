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

      $findAndReplace = [
        "**API_URL**" => config("app.url"),
        "**DISPLAY_ID**" => $display->id,
        "**DISPLAY_API_TOKEN**" => $request->bearerToken(),
        "**PUSHER_APP_KEY**" => config("app.pusher_app_key"),
        "**PUSHER_APP_CLUSTER**" => config("app.pusher_app_cluster"),
        "**DOCKER_TAG**" => config("app.env") === "production" ? "production" : "staging",
        "**DOCKER_ACCESS_TOKEN**" => config("app.docker_image_download_access_token"),
      ];

      $installScript = Storage::disk("local")->get("app-installation/install-bash-script.sh");
      foreach ($findAndReplace as $find => $replace) {
        $installScript = Str::replace($find, $replace, $installScript);
      }

      return response($installScript, 200)
        ->header('Content-Type', 'text/plain');
    }

    return response()->json(['message' => 'Not Found!'], 404);
  }
}
