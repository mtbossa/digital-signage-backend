<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DockerInstallerDownloadController extends Controller
{
  public function __invoke(Request $request): StreamedResponse
  {
    $appEnv = config("app.env"); # development, staging or production

    if ($appEnv !== "production" && $appEnv !== "staging") {
      $appEnv = "development";
    }

    return Storage::disk("local")->download("app-installation/docker-compose-$appEnv.yml");
  }
}
