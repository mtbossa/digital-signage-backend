<?php

namespace App\Models;

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
      'observation'
    ];

  public function display(): BelongsTo
  {
    return $this->belongsTo(Display::class);
  }

}
