<?php

namespace Tests\Feature\Raspberry\Traits;

use App\Models\Raspberry;

trait RaspberryTestsTrait
{
    private Raspberry $raspberry;

    private function _makeRaspberry(array $data = null): Raspberry
    {
        return Raspberry::factory()->make($data);
    }

    private function _createRaspberry(array $data = null): Raspberry
    {
        return Raspberry::factory()->create($data);
    }
}
