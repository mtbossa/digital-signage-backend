<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaDownloadController extends Controller
{
    public function __invoke(Request $request, string $filename): StreamedResponse|array
    {
        $media = Media::query()->where('filename', $filename)->firstOrFail();
        if (request()->has('temp_url')) {
            return ['temp_url' => Storage::temporaryUrl($media->path, now()->addMinutes(10))];
        }

        return Storage::download($media->path);
    }
}
