<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

  public function recurrence(): BelongsTo
  {
    return $this->belongsTo(Recurrence::class);
  }

  public function displays(): BelongsToMany
  {
    return $this->belongsToMany(Display::class);
  }
}
