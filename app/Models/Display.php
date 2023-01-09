<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Display extends Model
{
  use HasFactory, Notifiable;

  protected $fillable
    = [
      'name', 'size', 'width', 'height', 'observation',
      'store_id', 'pairing_code_id'
    ];

  public function raspberry(): HasOne
  {
    return $this->hasOne(Raspberry::class);
  }
  
  public function pairing_code(): BelongsTo
  {
      return $this->belongsTo(PairingCode::class);
  }

  public function posts(): BelongsToMany
  {
    return $this->belongsToMany(Post::class);
  }

  public function store(): BelongsTo
  {
    return $this->belongsTo(Store::class);
  }
}
