<?php

namespace App\Jobs;

use App\Models\PairingCode;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpirePairingCode implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public bool $deleteWhenMissingModels = true;

  public function __construct(public PairingCode $pairing_code)
  {
    //
  }

  public function handle()
  {
    $this->pairing_code->delete();
  }
}
