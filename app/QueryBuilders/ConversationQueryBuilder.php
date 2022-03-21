<?php

namespace App\QueryBuilders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ConversationQueryBuilder extends Builder
{
    public function matchesQueryForParticipant(string $query, User $participant)
    {
        $this
            ->select([
                'conversations.id',
                'participant.id as participant_id',
                'participant.name as participant_name',
                'messages.content as message_content',
            ])
            ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'conversations.id')
            ->join('conversation_participants as cp2', 'cp2.conversation_id', '=', 'conversations.id')
            ->join('users as participant', 'participant.id', '=', 'cp2.participant_id')
            ->leftJoin('messages', 'messages.conversation_id', '=', 'conversations.id')
            ->where('cp.participant_id', $participant->id)
            ->where('participant.id', '<>', $participant->id)
            ->where(function (Builder $builder) use ($query) {
                return $builder
                    ->whereFulltext('participant.name', $query)
                    ->orWhereFulltext('messages.content', $query);
            })
            ->orderByRaw("match (`participant`.`name`) against (? in natural language mode) desc", [$query])
            ->orderByRaw("match (`messages`.`content`) against (? in natural language mode) desc", [$query])
        ;

        return $this;
    }

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
