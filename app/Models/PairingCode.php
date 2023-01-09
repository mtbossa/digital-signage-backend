<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PairingCode extends Model
{
    use HasFactory;

    protected $table = 'pairing_codes';
  
    protected $fillable = ['code', 'expires_at'];

    public function display(): HasOne
    {
        return $this->hasOne(Display::class);
    }
}
