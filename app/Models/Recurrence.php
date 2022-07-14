<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function filteredRecurrence(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return array_filter([
                    'isoweekday' => $attributes['isoweekday'],
                    'day'        => $attributes['day'],
                    'month'      => $attributes['month'],
                    'year'       => $attributes['year'],
                ], function ($val) {
                    return !is_null($val);
                });

            }
        );
    }

    protected function recurrIsoWeekDay(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $byDayStrings = [
                    'MO',
                    'TU',
                    'WE',
                    'TH',
                    'FR',
                    'SA',
                    'SU'
                ];

                // -1 because isoweekday 1 => 'MO' is index 0
                return $byDayStrings[$attributes['isoweekday'] - 1];
            }
        );
    }
}
