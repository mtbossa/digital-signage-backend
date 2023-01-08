<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PairingCode extends Model
{
    use HasFactory;

    protected $table = 'pairing_codes';
  
    protected $fillable = ['code'];
}
