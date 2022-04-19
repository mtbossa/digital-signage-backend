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
    $this->url = $this->invitation->generateFrontendInvitationUrl();
  }


  public function build()
  {
    return $this->markdown('emails.users.invitation');
  }
}
