<?php

namespace App\Http\Controllers;

use App\Http\Requests\Recurrence\StoreRecurrenceRequest;
use App\Http\Requests\Recurrence\UpdateRecurrenceRequest;
use App\Models\Recurrence;
use Illuminate\Database\Eloquent\Collection;

class RecurrenceController extends Controller
{
  public function index(): Collection
  {
    return Recurrence::all();
  }

  public function store(StoreRecurrenceRequest $request): Recurrence
  {
    return Recurrence::create($request->validated());
  }

  public function show(Recurrence $recurrence): Recurrence
  {
    return $recurrence;
  }

  public function update(UpdateRecurrenceRequest $request, Recurrence $recurrence): Recurrence
  {
    $recurrence->update($request->validated());
    return $recurrence;
  }

  public function destroy(Recurrence $recurrence): bool
  {
    return $recurrence->delete();
  }
}
