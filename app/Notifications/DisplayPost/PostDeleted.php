<?php

namespace App\Notifications\DisplayPost;

use App\Models\Display;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PostDeleted extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(public Display $display, public int $post_id, public int $media_id)
  {
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   *
   * @return array
   */
  public function via($notifiable): array
  {
    return ['broadcast'];
  }

  /**
   * Get the broadcastable representation of the notification.
   *
   * @param  mixed  $notifiable
   *
   * @return BroadcastMessage
   */
  public function toBroadcast($notifiable): BroadcastMessage
  {
    $postAmountThatDependsOnDeletedPostMedia = $this->display->posts()->where(function (
      Builder $query
    ) {
      $query->select(["id as post_id", "media_id"]);
      $query->where("post_id", "<>", $this->post_id);
      $query->where('media_id', $this->media_id);
    })->count("posts.id");

    $canDeleteMedia = $postAmountThatDependsOnDeletedPostMedia === 0;

    return new BroadcastMessage([
      'post_id' => $this->post_id, 'media_id' => $this->media_id, 'canDeleteMedia' => $canDeleteMedia
    ]);
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   *
   * @return array
   */
  public function toArray($notifiable)
  {
    return [
    ];
  }
}
