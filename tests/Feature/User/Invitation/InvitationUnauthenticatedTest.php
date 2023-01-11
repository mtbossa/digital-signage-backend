<?php

namespace Tests\Feature\User\Invitation;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\Feature\User\Invitation\Traits\InvitationTestsTrait;
use Tests\TestCase;

class InvitationUnauthenticatedTest extends TestCase
{
    use RefreshDatabase, InvitationTestsTrait, AuthUserTrait, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->invitation = $this->_createWithTokenInvitation([
            'inviter' => $this->user->id, 'is_admin' => $this->user->is_admin,
        ]);
    }

    /** @test */
    public function unauthenticated_user_may_fetch_single_invitation()
    {
        $this->getJson(route('invitations.show',
            $this->invitation->token))->assertOk()->assertJson($this->invitation->toArray());
    }

    /** @test */
    public function ensure_not_found_is_returned_when_trying_to_acess_already_registered_invitation()
    {
        $invitation = $this->_createWithTokenInvitation([
            'inviter' => $this->user->id, 'registered_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        $this->getJson(route('invitations.show',
            $invitation->token))->assertNotFound();
    }
}
