<?php

namespace App\Http\Controllers;

use App\Models\Raspberry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RaspberryInstallerDownloadController extends Controller
{
  public function __invoke(Request $request): Response|JsonResponse|Application|ResponseFactory
  {
    $authenticated = Auth::user();
    $isRaspberry = $authenticated instanceof Raspberry;

    if (!$isRaspberry) {
      return response()->json(['message' => 'Not Found!'], 404);
    }

    $correctAppEnv = $this->getCorrectAppEnv();
    
    $customStartupScript = Storage::disk("local")->get("app-installation/startup-script-$correctAppEnv.sh");
    $installScript = Storage::disk("local")->get("app-installation/default-install-script.sh");

    $installScript = Str::replace("**STARTUP_SCRIPT**", $customStartupScript, $installScript);

    $findAndReplace = [
      "**NODE_ENV**" => $correctAppEnv, "**RASPBERRY_API_TOKEN**" => $request->bearerToken(),
      "**RASPBERRY_ID**" => $authenticated->id, "**APP_GITHUB_REPO_URL**" => config("app.app_github_repo_url"),
    ];
    $findAndReplace["**API_URL**"] = config("app.url");
    $findAndReplace["**PUSHER_CLUSTER**"] = env("PUSHER_APP_CLUSTER", 'sa1');
    $findAndReplace["**PUSHER_APP_KEY**"] = config("broadcasting.connections.pusher.key");
    
    foreach ($findAndReplace as $find => $replace) {
      $installScript = Str::replace($find, $replace, $installScript);
    }

    return response($installScript, 200)->header('Content-Type', 'text/plain');
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
