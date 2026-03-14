<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\CompanyContext;
use App\Models\Conversation;
use App\Models\Owner;
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
            ->where('staff_user_id', $user->id)
            ->with(['staffUser', 'ownerUser', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return Inertia::render('Staff/Messages/Index', [
            'conversations' => ConversationResource::collection($conversations),
            'owners'        => fn () => Owner::with('user')->get()->map(fn ($o) => [
                'id'      => $o->user_id,
                'name'    => $o->name,
            ]),
        ]);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $this->authorizeConversation($conversation);

        $user = $request->user();
        $this->conversationService->markAsRead($conversation, $user);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->paginate(50);

        $conversation->load(['staffUser', 'ownerUser']);

        return Inertia::render('Staff/Messages/Show', [
            'conversation' => new ConversationResource($conversation),
            'messages'     => MessageResource::collection($messages),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'body'          => ['required', 'string', 'max:5000'],
        ]);

        $company = app(CompanyContext::class);
        $user = $request->user();

        $conversation = $this->conversationService->findOrCreate(
            $company->id,
            $user->id,
            $validated['owner_user_id'],
        );

        $this->conversationService->sendMessage($conversation, $user, $validated['body']);

        return redirect()->route('staff.messages.show', [
            'company'      => $company->slug,
            'conversation' => $conversation->id,
        ])->with('success', 'Message sent.');
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $this->conversationService->sendMessage($conversation, $request->user(), $validated['body']);

        return back()->with('success', 'Reply sent.');
    }

    public function markRead(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($conversation);
        $this->conversationService->markAsRead($conversation, $request->user());

        return back();
    }

    private function authorizeConversation(Conversation $conversation): void
    {
        $company = app(CompanyContext::class);

        if ($conversation->company_id !== $company->id) {
            abort(403);
        }
    }
}
