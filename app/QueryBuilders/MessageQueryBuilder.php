<?php

namespace App\QueryBuilders;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MessageQueryBuilder extends Builder
{
    public function whereRead()
    {
        return $this->whereNotNull('read_at');
    }

    public function whereUnread()
    {
        return $this->whereNull('read_at');
    }

    public function whereSenderIs(User $sender)
    {
        return $this->where('sender_id', $sender->id);
    }

    public function whereRecipientIs(User $recipient)
    {
        return $this->where('sender_id', '<>', $recipient->id);
    }

    public function beforeMessage(Message $message)
    {
        return $this->where('id', '<=', $message->id);
    }

    public function markAsRead()
    {
        return $this->update(['read_at' => now()]);
    }
}
