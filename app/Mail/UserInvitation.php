<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public string $url;

  public function __construct(public Invitation $invitation)
  {
    $this->afterCommit();
    $this->url = $this->_generateFrontendInvitationUrl();
  }

  private function _generateFrontendInvitationUrl()
  {
    $front_url = env('APP_FRONT_URL');
    return url("{$front_url}/invitations/{$this->invitation->token}");
  }

  public function build()
  {
    return $this->markdown('emails.users.invitation');
  }
}
