<?php

namespace App\Mail;

use App\Models\Display;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstallationLink extends Mailable
{
  use Queueable, SerializesModels;

  public string $url;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct(private readonly Display $display, private $token)
  {
    $this->afterCommit();
    $this->url = $this->display->generateInstallationUrl($token);
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->markdown('emails.displays.installation-link')
      ->with([
        'installerUrl' => $this->url,
      ]);
  }
}
