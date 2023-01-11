<?php

namespace Tests\Feature\Media\Traits;

use App\Models\Media;

trait MediaTestsTrait
{
    private Media $media;

    private function _makeMedia(array $data = null): Media
    {
        return Media::factory()->make($data);
    }

    private function _createMedia(array $data = null): Media
    {
        return Media::factory()->create($data);
    }
}
