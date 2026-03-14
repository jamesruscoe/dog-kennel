<?php

namespace App\Http\Middleware;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     * These are available on every Inertia page via usePage().props.
     *
     * @return array<string, mixed>
     */
    private function unreadMessagesCount(mixed $user): int
    {
        $field = $user->isOwner() ? 'owner_user_id' : 'staff_user_id';

        return \App\Models\Message::query()
            ->whereIn('conversation_id', Conversation::where($field, $user->id)->select('id'))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role?->value,
                    'company_id' => $user->company_id,
                ] : null,
            ],
            // company is shared by ResolveCompanyFromPath for tenant routes;
            // default null ensures the prop exists on root/platform routes too.
            'company' => null,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
            'unread_notifications_count' => $user
                ? $user->unreadNotifications()->count()
                : 0,
            'unread_messages_count' => $user
                ? $this->unreadMessagesCount($user)
                : 0,
            'csrf_token' => csrf_token(),
        ];
    }
}
