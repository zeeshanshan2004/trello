<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->take(20)->get();
        $unread = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications->map(fn($n) => [
                'id'         => $n->id,
                'message'    => $n->data['message'],
                'board_id'   => $n->data['board_id'] ?? null,
                'board_name' => $n->data['board_name'] ?? null,
                'card_id'    => $n->data['card_id'] ?? null,
                'list_id'    => $n->data['list_id'] ?? null,
                'type'       => $n->data['type'] ?? 'activity',
                'read'       => !is_null($n->read_at),
                'diff'       => $n->created_at->diffForHumans(),
            ]),
            'unread' => $unread,
        ]);
    }

    public function markRead(Request $request)
    {
        try {
            if ($request->id) {
                // Try to mark specific notification as read
                $updated = Auth::user()->notifications()->where('id', $request->id)->update(['read_at' => now()]);
                // If notification doesn't exist (old ID), just continue without error
            } else {
                Auth::user()->unreadNotifications->markAsRead();
            }
        } catch (\Exception $e) {
            // Silently handle any errors (e.g., invalid notification ID)
        }
        return response()->json(['success' => true]);
    }
}
