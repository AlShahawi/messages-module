<?php

namespace App;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessageSender
{
    private User $from;
    private User $to;
    private string $message;

    public function from(User $user)
    {
        $this->from = $user;

        return $this;
    }

    public function to(User $user)
    {
        $this->to = $user;

        return $this;
    }

    public function content(string $message)
    {
        $this->message = $message;

        return $this;
    }

    public function send(string $message = null)
    {
        if (is_null($message)) {
            $message = $this->message;
        }

        if (is_null($message)) {
            throw new \Exception('Cannot send an empty message.');
        }

        $conversation = Conversation::query()
            ->whereHasExactParticipants($participantIds = [$this->from->id, $this->to->id])
            ->first();

        if (is_null($conversation)) {
            /** @var Conversation $conversation */
            $conversation = tap(Conversation::make()
                ->forceFill([
                    'created_by' => $this->from->id,
                ]))->save();

            $conversation->participants()->attach($participantIds);
        }

        $message = Message::make()->forceFill([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->from->id,
            'content' => $message,
        ]);

        return tap($message)->save();
    }
}
