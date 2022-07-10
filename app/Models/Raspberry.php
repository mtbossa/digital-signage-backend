<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Raspberry extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable
        = [
            'mac_address', 'short_name', 'serial_number', 'last_boot',
            'observation'
        ];

    public function display(): BelongsTo
    {
        return $this->belongsTo(Display::class);
    }

}
