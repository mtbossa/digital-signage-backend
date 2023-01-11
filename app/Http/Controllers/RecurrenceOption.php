<?php

namespace App\Http\Controllers;

use App\Models\Recurrence;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class RecurrenceOption extends Controller
{
    public function __invoke(Request $request): Collection
    {
        return Recurrence::all(['id', 'description']);
    }
}
