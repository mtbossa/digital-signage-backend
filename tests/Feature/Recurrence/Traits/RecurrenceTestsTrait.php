<?php

namespace Tests\Feature\Recurrence\Traits;

use App\Models\Recurrence;

trait RecurrenceTestsTrait
{
    private Recurrence $recurrence;

    private function _makeRecurrence(array $data = null): Recurrence
    {
        return Recurrence::factory()->make($data);
    }

    private function _createRecurrence(array $data = null): Recurrence
    {
        return Recurrence::factory()->create($data);
    }
}
