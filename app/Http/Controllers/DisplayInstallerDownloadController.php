<?php

namespace App\Http\Controllers;

use App\Models\Display;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisplayInstallerDownloadController extends Controller
{
  public function __invoke(Request $request, Display $display): StreamedResponse|JsonResponse
  {
    $authenticated = Auth::user();
    if ($authenticated instanceof Display) {
      if ($authenticated->id !== $display->id) {
        return response()->json(['message' => 'Not Found!'], 404);
      }

      $path = Storage::put('file.txt', "oi");

      return Storage::download($path);
    }

    return response()->json(['message' => 'Not Found!'], 404);
  }
}
