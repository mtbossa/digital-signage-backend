<?php

namespace App\Models;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Raspberry extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable
      = [
          'mac_address', 'short_name', 'serial_number', 'last_boot',
          'observation', 'display_id',
      ];

    public function display(): BelongsTo
    {
        return $this->belongsTo(Display::class);
    }

    public function generateInstallationUrl(): string|UrlGenerator|Application
    {
        $apiUrl = config('app.url');

        return url("{$apiUrl}/api/raspberry/installer/download");
    }
}
