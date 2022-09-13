<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DockerInstallerDownloadController extends Controller
{
  public function __invoke(Request $request): StreamedResponse
  {
    return Storage::download("app-installation/docker-compose-production.yml");
  }
}
