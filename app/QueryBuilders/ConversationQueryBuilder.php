<?php

namespace App\QueryBuilders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ConversationQueryBuilder extends Builder
{
    public function whereHasExactParticipants(array $participants)
    {
        return $this->withCount([
                'conversationParticipants' => function ($q) use ($participants) {
                    return $q->whereColumn('conversation_participants.conversation_id', 'conversations.id')
                        ->whereIn('conversation_participants.participant_id', $participants);
                },
            ]
        )->having('conversation_participants_count', count($participants));
    }

    public function forParticipant(User $participant)
    {
        return $this->whereHas('participants', function ($builder) use ($participant) {
            $builder->where('conversation_participants.participant_id', $participant->id);
        });
    }
}
