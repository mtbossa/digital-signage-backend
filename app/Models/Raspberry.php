<?php

namespace App\Models;

use Database\Factories\RaspberryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\Raspberry
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static RaspberryFactory factory(...$parameters)
 * @method static Builder|Raspberry newModelQuery()
 * @method static Builder|Raspberry newQuery()
 * @method static Builder|Raspberry query()
 * @method static Builder|Raspberry whereCreatedAt($value)
 * @method static Builder|Raspberry whereId($value)
 * @method static Builder|Raspberry whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Raspberry extends Model
{
  use HasFactory, HasApiTokens;

  protected $fillable = ['mac_address', 'short_name', 'serial_number', 'last_boot', 'observation'];

  public function display(): BelongsTo
  {
    return $this->belongsTo(Display::class);
  }

}
