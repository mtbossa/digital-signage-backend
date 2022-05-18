<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class DisplayPostsRecurrenceResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  Request  $request
   * @return array|Arrayable|JsonSerializable
   */
  public function toArray($request)
  {
    return ['day' => $this->day, 'isoweekday' => $this->isoweekday, 'month' => $this->month, 'year' => $this->year];
  }
}
