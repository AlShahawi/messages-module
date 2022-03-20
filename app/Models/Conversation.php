<?php

namespace App\Models;

use App\QueryBuilders\ConversationQueryBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    public function conversationParticipants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'participant_id')
            ->using(ConversationParticipant::class)
            ->withPivot(['archived_at'])
            ->withTimestamps();
    }

    public function newEloquentBuilder($query)
    {
        return new ConversationQueryBuilder($query);
    }
}
