<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurrence extends Model
{
  use HasFactory;

  protected $fillable = [
    'description',
    'isoweekday',
    'day',
    'month',
    'year',
  ];

  public function posts()
  {
    return $this->hasMany(Post::class);
  }
}
