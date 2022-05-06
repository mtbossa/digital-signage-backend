<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisplayPostsResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  Request  $request
   * @return array
   */
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'start_date' => $this->start_date,
      'end_date' => $this->end_date,
      'start_time' => $this->start_time,
      'end_time' => $this->end_time,
      'expose_time' => $this->expose_time,
      $this->mergeWhen($this->whenLoaded('medias'), [
        'media' => [
          'path' => $this->media->path,
          'type' => $this->media->type
        ],
      ]),
    ];
  }
}
