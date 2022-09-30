<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaDownloadController extends Controller
{
  /**
   * Handle the incoming request.
   *
   * @param  Request  $request
   * @return StreamedResponse
   */
  public function __invoke(Request $request, string $filename)
  {
    ini_set('max_execution_time', 180);
    $media = Media::query()->where('filename', $filename)->firstOrFail();
    $contents = Storage::get($media->path);
    $size = Storage::size($media->path);
    return Storage::download($media->path);
  }
}
