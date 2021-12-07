<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'schedule_id' => $this->get('schedule_id'),
            'time' => $this->get('time')->format('G:i'),
            'activity' => new ActivityResource($this->get('activity'))
        ];
    }
}
