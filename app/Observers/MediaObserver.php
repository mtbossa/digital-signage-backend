<?php

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaObserver
{
    public function deleted(Media $media): void
    {
        Storage::delete($media->path);
    }
}
