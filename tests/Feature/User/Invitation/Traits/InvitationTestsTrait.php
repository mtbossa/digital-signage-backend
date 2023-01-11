<?php

namespace Tests\Feature\User\Invitation\Traits;

use App\Models\Invitation;

trait InvitationTestsTrait
{
    private Invitation $invitation;

    private function _makeInvitation(array $data = null): Invitation
    {
        return Invitation::factory()->make($data);
    }

    private function _createInvitation(array $data = null): Invitation
    {
        return Invitation::factory()->create($data);
    }

    private function _createWithTokenInvitation(array $data = null): Invitation
    {
        return Invitation::factory()->withToken()->create($data);
    }
}
