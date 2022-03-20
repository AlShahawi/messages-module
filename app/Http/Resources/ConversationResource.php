<?php

namespace App\Http\Resources;

use App\Models\Conversation;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Conversation */
class ConversationResource extends JsonResource
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
            'name' => $this->theOtherPartyName($request->user()),
            'last_message' => new MessageResource($this->whenLoaded('lastMessage')),
        ];
    }

    public function theOtherPartyName($currentUser, $defaultString = 'Application User')
    {
        return $this->theOtherPartyOf($currentUser)->name ?? $defaultString;
    }
}
