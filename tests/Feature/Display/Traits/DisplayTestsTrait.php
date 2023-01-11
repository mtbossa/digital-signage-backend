<?php

namespace Tests\Feature\Display\Traits;

use App\Models\Display;
use App\Models\Media;
use App\Models\Raspberry;

trait DisplayTestsTrait
{
    private Display $display;

    private Raspberry $raspberry;

    private Media $media;

    private function _makeDisplay(array $data = null): Display
    {
        return Display::factory()->make($data);
    }

    private function _createDisplay(array $data = null): Display
    {
        return Display::factory()->create($data);
    }
}
