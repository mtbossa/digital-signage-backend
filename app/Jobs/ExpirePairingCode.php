<?php

namespace App\Jobs;

use App\Models\PairingCode;
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
        if ($this->pairing_code->display) {
            $this->pairing_code->display->delete();
        }
        $this->pairing_code->delete();
    }
}
