<?php

namespace Tests\Feature\User\Invitation;

use App\Mail\UserInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\Feature\User\Invitation\Traits\InvitationTestsTrait;
use Tests\TestCase;

class InvitationEmailTest extends TestCase
{
    use RefreshDatabase, InvitationTestsTrait, AuthUserTrait, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->_authUser();
        $this->invitation = $this->_createWithTokenInvitation(['inviter' => $this->user->id]);
    }

    /** @test */
    public function assert_invitation_email_have_all_text_info()
    {
        $mailable = new UserInvitation($this->invitation);

        $front_url = url(env('APP_FRONT_URL'));
        $correct_invitation_path = "{$front_url}/convites/{$this->invitation->token}/aceitar";

        $mailable->assertSeeInHtml('Para aceitar, clique no botão abaixo ou no link caso o botão não esteja funcionando');
        $mailable->assertSeeInHtml($correct_invitation_path);
    }
}
