<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $messages = $conversation
            ->messages()
            ->with('sender')
            ->latest('id')
            ->cursorPaginate(25);

        return MessageResource::collection($messages);
    }

    public function markAsRead(Request $request, Conversation $conversation, Message $message)
    {
        $conversation
            ->messages()
            ->beforeMessage($message)
            ->whereUnread($message)
            ->whereRecipientIs($request->user())
            ->markAsRead();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
