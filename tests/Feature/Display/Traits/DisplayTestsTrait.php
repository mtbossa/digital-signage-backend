<?php

namespace Tests\Feature\Display\Traits;

use App\Models\Display;

trait DisplayTestsTrait
{
  private Display $display;

  private function _makeDisplay(array $data = null): Display
  {
    return Display::factory()->make($data);
  }

  private function _createDisplay(array $data = null): Display
  {
    Display::factory()->create($data);
    return Display::first();
  }
}
