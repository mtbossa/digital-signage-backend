<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Raspberry
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\RaspberryFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Raspberry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Raspberry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Raspberry query()
 * @method static \Illuminate\Database\Eloquent\Builder|Raspberry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Raspberry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Raspberry whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Raspberry extends Model
{
  use HasFactory;

  protected $fillable = ['mac_address', 'short_name', 'serial_number', 'last_boot', 'observation'];
}
