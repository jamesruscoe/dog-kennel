<?php

namespace App\Http\Controllers\Kennel;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request): Response|JsonResponse
    {
        // Pinia store polls this via AJAX — return JSON
        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $this->notificationService->unreadFor($request->user()),
            ]);
        }

        // Browser navigation — render the Inertia page with all notifications
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(30)
            ->through(fn ($n) => [
                'id'         => $n->id,
                'type'       => class_basename($n->type),
                'data'       => $n->data,
                'read_at'    => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        // Mark all as read on page visit
        $this->notificationService->markAllRead($request->user());

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $this->notificationService->markRead($request->user(), $id);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllRead($request->user());

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
