<?php

namespace App\Services;

use Illuminate\Foundation\Testing\WithFaker;

class PairingCodeGeneratorService
{
  use WithFaker;
  public function generate(): string {
    return str_shuffle(substr(md5(microtime()), 2,6));
  }
}
