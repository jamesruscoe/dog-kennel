<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\CompanyContext;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function __construct(private readonly ConversationService $conversationService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->where('owner_user_id', $user->id)
            ->with(['staffUser', 'ownerUser', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return Inertia::render('Owner/Messages/Index', [
            'conversations' => ConversationResource::collection($conversations),
        ]);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $this->authorizeConversation($conversation, $request);

        $user = $request->user();
        $this->conversationService->markAsRead($conversation, $user);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->paginate(50);

        $conversation->load(['staffUser', 'ownerUser']);

        return Inertia::render('Owner/Messages/Show', [
            'conversation' => new ConversationResource($conversation),
            'messages'     => MessageResource::collection($messages),
        ]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($conversation, $request);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $this->conversationService->sendMessage($conversation, $request->user(), $validated['body']);

        return back()->with('success', 'Reply sent.');
    }

    public function markRead(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($conversation, $request);
        $this->conversationService->markAsRead($conversation, $request->user());

        return back();
    }

    private function authorizeConversation(Conversation $conversation, Request $request): void
    {
        $company = app(CompanyContext::class);

        if ($conversation->company_id !== $company->id || $conversation->owner_user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
