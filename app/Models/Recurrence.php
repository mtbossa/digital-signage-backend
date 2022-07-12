<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Recurrence extends Model
{
    use HasFactory;

    protected $fillable
        = [
            'description',
            'isoweekday',
            'day',
            'month',
            'year',
        ];

    public static function getOnlyNotNullRecurrenceValues(Recurrence $recurrence
    ): Collection {
        return Collection::make($recurrence->getAttributes())
            ->filter(fn($item, $key) => $item
                && ($key === 'isoweekday' || $key === 'day'
                    || $key === 'month'
                    || $key === 'year')
            );
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
