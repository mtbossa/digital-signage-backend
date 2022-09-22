<?php

namespace App\Http\Controllers;

use App\Http\Requests\Recurrence\StoreRecurrenceRequest;
use App\Http\Requests\Recurrence\UpdateRecurrenceRequest;
use App\Models\Recurrence;
use App\Notifications\DisplayPost\PostDeleted;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecurrenceController extends Controller
{
  public function index(Request $request): LengthAwarePaginator
  {
    return Recurrence::query()->paginate($request->size);
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

  public function destroy(Recurrence $recurrence)
  {
    $recurrence->load("posts.displays");

    DB::transaction(function () use ($recurrence) {
      $posts = $recurrence->posts;

      foreach ($posts as $post) {
        foreach ($post->displays as $display) {
          $notification = new PostDeleted($display, $post->id, $post->media->id);

          $display->notify($notification);
        }
      }

      $recurrence->delete();
    });


    return response("", 200);
  }
}
