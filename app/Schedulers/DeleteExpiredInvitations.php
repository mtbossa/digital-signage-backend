<?php

namespace App\Schedulers;

use App\Models\Invitation;

class DeleteExpiredInvitations
{
    public function __invoke(): void
    {
        $now = now()->toISOString();
        $nowSubDay = now()->subDay()->toISOString();
        // Order matters in this where query. [$now, $nowSubDay] would bring wrong data, must be , [$nowSubDay, $now]
        $twentyFourHourInvitations = Invitation::query()->select(['id'])->whereNotBetween('created_at',
            [$nowSubDay, $now])->get();
        Invitation::destroy($twentyFourHourInvitations->toArray());
    }
}
