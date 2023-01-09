<?php

namespace App\Services;

use Carbon\Carbon;

class PairingCodeGeneratorService
{
    public function generate(): array
    {
        $code = $this->makeRandomCode();
        $expires_at = $this->expiresWhen();
        return ['code' => $code, 'expires_at' => $expires_at];
    }

    private function makeRandomCode(): string
    {
        return str_shuffle(substr(md5(microtime()), 2, 6));
    }

    private function expiresWhen(): Carbon
    {
        return now()->addMinutes(5);
    }
}
