<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'name' => $this->name,
            'sound' => $this->sound,
            'minutes' => $this->minutes,
            'commands' => $this->when($this->commands, new CommandCollection($this->commands)),
            'frequency' => $this->frequency->id ?? null
        ];
    }
}
