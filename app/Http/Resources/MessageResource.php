<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'id' => $this->id,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'content' => $this->content,
            // if the current user is the sender for this message, then display when was the message read.
            'read_at' => $this->sender_id === $request->user()->id ? $this->read_at : null,
            // return creation date in standard format, let the front-end format it whatever he wants.
            'created_at' => $this->created_at,
        ];
    }
}
