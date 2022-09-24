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

      $nodeEnv = config("app.env") === "production" ? "production" : "staging";
      $dockerTag = config("app.env") === "production" ? "latest" : "staging";
      $dockerImage = config("app.docker_image");
      $dockerCompleteImage = "$dockerImage:$dockerTag";

      $findAndReplace = [
        "**API_URL**" => config("app.url"),
        "**NODE_ENV**" => $nodeEnv,
        "**DISPLAY_ID**" => $display->id,
        "**DISPLAY_API_TOKEN**" => $request->bearerToken(),
        "**DOCKER_ACCESS_TOKEN**" => config("app.docker_image_download_access_token"),
        "**DOCKER_COMPLETE_IMAGE**" => $dockerCompleteImage,
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
