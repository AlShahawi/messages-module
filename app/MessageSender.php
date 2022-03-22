<?php

namespace App;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessageSender
{
    private User $from;
    private ?User $to = null;
    private string $message;
    private ?Conversation $conversation = null;

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

    public function conversation(Conversation $conversation)
    {
        $this->conversation = $conversation;

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

        if (! $this->conversation) {
            $this->conversation = Conversation::query()
                ->whereHasExactParticipants($participantIds = [$this->from->id, $this->to->id])
                ->first();
        }

        if (is_null($this->conversation)) {
            /** @var Conversation $conversation */
            $this->conversation = tap(Conversation::make()
                ->forceFill([
                    'created_by' => $this->from->id,
                ]))->save();

            $this->conversation->participants()->attach($participantIds);
        }

        $message = Message::make()->forceFill([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->from->id,
            'content' => $message,
        ]);

        return tap($message)->save();
    }
}
