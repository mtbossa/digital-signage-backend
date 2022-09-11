<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Display extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable
        = [
            'name', 'size', 'width', 'height', 'touch', 'observation',
            'store_id'
        ];

    public function raspberry(): HasOne
    {
        return $this->hasOne(Raspberry::class);
    }

  public function posts(): BelongsToMany
  {
    return $this->belongsToMany(Post::class);
  }

  public function store(): BelongsTo
  {
    return $this->belongsTo(Store::class);
  }

  public function generateInstallationUrl()
  {
    $apiUrl = env('APP_URL');
    return url("{$apiUrl}/api/displays/{$this->id}/installer/download");
  }
}
