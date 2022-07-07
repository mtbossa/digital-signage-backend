<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Display extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'size', 'width', 'height', 'touch', 'observation'];

  public function raspberry(): HasOne
  {
    return $this->hasOne(Raspberry::class);
  }

  public function posts(): BelongsToMany
  {
    return $this->belongsToMany(Post::class);
  }
}
