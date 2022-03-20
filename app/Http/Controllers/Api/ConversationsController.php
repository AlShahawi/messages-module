<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationsController extends Controller
{
    public function index(Request $request)
    {
        $conversations = Conversation::query()
            ->with('lastMessage', 'participants')
            ->forParticipant($request->user())
            ->simplePaginate();

        return ConversationResource::collection($conversations);
    }
}
