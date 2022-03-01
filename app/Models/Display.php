<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Display
 *
 * @property int $id
 * @property string $name
 * @property string $size
 * @property int $width
 * @property int $height
 * @property bool $touch
 * @property string|null $observation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\DisplayFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Display newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Display newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Display query()
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereObservation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereTouch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Display whereWidth($value)
 * @mixin \Eloquent
 */
class Display extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'size', 'width', 'height', 'touch', 'observation'];

  public function raspberry()
  {
    return $this->hasOne(Raspberry::class);
  }
}
