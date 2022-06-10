<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
  use HasFactory;

  protected $table = 'medias';

  protected $fillable = ['description', 'type', 'filename', 'extension', 'path', 'size_kb'];

  public function posts(): HasMany
  {
    return $this->hasMany(Post::class);
  }
}
