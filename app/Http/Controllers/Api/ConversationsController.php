<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ConversationsController extends Controller
{
    public function index(Request $request)
    {
        $conversations = Conversation::query()
            ->with('lastMessage.sender', 'participants')
            ->forParticipant($request->user())
            ->simplePaginate();

        return ConversationResource::collection($conversations);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query'=> ['required', 'string', 'min:6'],
        ]);

        $conversations = Conversation::query()
            ->matchesQueryForParticipant($request->query('query'), $request->user())
            ->limit(25) // limit search results, for optimization purposes until necessary.
            ->get();

        // TODO: it's better to wrap the result into a transformer.
        return ['data' => $conversations];
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $exists = $conversation
            ->conversationParticipants()
            ->where('participant_id', $request->user()->id)
            ->exists();

        // TODO: it is better to move this logic in a policy.
        abort_unless($exists, Response::HTTP_FORBIDDEN);

        $messages = $conversation
            ->messages()
            ->with('sender')
            ->latest('id')
            ->cursorPaginate(25);

        return MessageResource::collection($messages);
    }

    public function markAsRead(Request $request, Conversation $conversation, Message $message)
    {
        $exists = $conversation
            ->conversationParticipants()
            ->where('participant_id', $request->user()->id)
            ->exists();
        $isNotOwnedByCurrentUser = $message->sender->isNot($request->user());

        // TODO: it is better to move this logic in a policy.
        abort_unless($exists && $isNotOwnedByCurrentUser, Response::HTTP_FORBIDDEN);

        $conversation
            ->messages()
            ->beforeMessage($message)
            ->whereUnread($message)
            ->whereRecipientIs($request->user())
            ->markAsRead();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function new(Request $request)
    {
        $request->validate([
            'participant_id' => ['required', Rule::notIn([$request->user()->id]), 'exists:users,id'],
            'content' => ['required', 'string', 'max:1024'],
        ]);

        $message = Message::compose()
            ->from($request->user())
            ->to(User::find($request->input('participant_id')))
            ->content($request->input('content'))
            ->send();

        return new ConversationResource($message->conversation->load('lastMessage.sender', 'participants'));
    }

    public function send(Request $request, Conversation $conversation)
    {
        $exists = $conversation
            ->conversationParticipants()
            ->where('participant_id', $request->user()->id)
            ->exists();

        // TODO: it is better to move this logic in a policy.
        abort_unless($exists, Response::HTTP_FORBIDDEN);

        $request->validate([
            'content' => ['required', 'string', 'max:1024'],
        ]);

        $message = Message::compose()
            ->from($request->user())
            ->conversation($conversation)
            ->content($request->input('content'))
            ->send();

        return new MessageResource($message->load('sender'));
    }
}
