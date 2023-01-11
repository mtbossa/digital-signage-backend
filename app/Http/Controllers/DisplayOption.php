<?php

namespace App\Http\Controllers;

use App\Models\Display;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DisplayOption extends Controller
{
    public function __invoke(Request $request): Collection
    {
        $columns = ['id', 'name'];
        $query = Display::query();

        if ($request->has('whereDoesntHaveRaspberry')) {
            $query->whereDoesntHave('raspberry');
        }

        if ($request->has('withIds')) {
            $withIdsArray = json_decode($request->withIds);
            if (count($withIdsArray) > 0) {
                $query->orWhereIn('id', $withIdsArray);
            }
        }

        return $query->get($columns);
    }
}
