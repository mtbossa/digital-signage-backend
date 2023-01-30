<?php

namespace App\Mail;

use App\Models\Raspberry;
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
  public function __construct(private readonly Raspberry $raspberry)
  {
    $this->afterCommit();
    $this->url = $this->raspberry->generateInstallationUrl();
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->markdown('emails.raspberries.installation-link')
      ->with([
        'installerUrl' => $this->url,
      ]);
  }
}
