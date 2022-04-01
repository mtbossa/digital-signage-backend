<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
  use HasFactory;

  protected $fillable = [
    'description',
    'start_date',
    'end_date',
    'start_time',
    'end_time',
    'expose_time',
  ];

  public function media(): BelongsTo
  {
    return $this->belongsTo(Media::class);
  }
}
