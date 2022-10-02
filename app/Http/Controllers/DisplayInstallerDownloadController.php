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

      $correctAppEnv = $this->getCorrectAppEnv();

      $findAndReplace = [
        "**NODE_ENV**" => $correctAppEnv,
        "**DISPLAY_ID**" => $display->id,
        "**DISPLAY_API_TOKEN**" => $request->bearerToken(),
        "**APP_GITHUB_REPO_URL**" => config("app.app_github_repo_url"),
      ];

      if ($correctAppEnv === "development") {
        // Appended necessary variables for development
        $findAndReplace["**API_URL**"] = config("app.url");
        $findAndReplace["**PUSHER_CLUSTER**"] = config("broadcasting.connections.pusher.options.cluster");
        $findAndReplace["**PUSHER_APP_KEY**"] = config("broadcasting.connections.pusher.key");
      }

      $installScript = Storage::disk("local")->get("app-installation/install-app-script-$correctAppEnv.sh");
      foreach ($findAndReplace as $find => $replace) {
        $installScript = Str::replace($find, $replace, $installScript);
      }

      return response($installScript, 200)
        ->header('Content-Type', 'text/plain');
    }

    return response()->json(['message' => 'Not Found!'], 404);
  }

  private function getCorrectAppEnv(): string
  {
    $appEnv = config("app.env");

    if ($appEnv === "production") {
      return "production";
    } else {
      if ($appEnv === "staging") {
        return "staging";
      } else {
        return "development";
      }
    }
  }
}
