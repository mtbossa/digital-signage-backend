<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaDownloadController extends Controller
{

  public function __invoke(Request $request, string $filename): StreamedResponse|string
  {
    $media = Media::query()->where('filename', $filename)->firstOrFail();
    if (request()->has("temp_url")) {
      return Storage::temporaryUrl($media->path, now()->addMinutes(10));
    }
    $contents = Storage::get($media->path);
    $size = Storage::size($media->path);
    return Storage::download($media->path);
  }
}
