<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get the notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $notifications = $user->notifications()
            ->take(10)
            ->get()
            ->map(function ($n) {
                $link = '#';
                $message = $n->data['message'] ?? 'New notification received.';
                
                if ($n->type === \App\Notifications\TicketUpdatedNotification::class) {
                    if (!empty($n->data['ticket_id'])) {
                        $link = route('tickets.show', $n->data['ticket_id']);
                    }
                } elseif ($n->type === \App\Notifications\PendingUserRegisteredNotification::class) {
                    $link = route('admin.users.index');
                }

                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'data' => $n->data,
                    'message' => $message,
                    'read_at' => $n->read_at,
                    'created_at_human' => $n->created_at ? $n->created_at->diffForHumans() : '',
                    'link' => $link,
                ];
            });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}
