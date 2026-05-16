<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'message'    => $n->message,
                'type'       => $n->type ?? 'status_change',
                'is_read'    => (bool) $n->is_read,
                'request_id' => $n->request_id,
                'created_at' => $n->created_at,
            ]);

        $unread = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data'    => $notifications,
            'unread'  => $unread,
        ]);
    }

    public function markRead(int $id): JsonResponse
    {
        Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}