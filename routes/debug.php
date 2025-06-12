<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-user', function () {
    if (auth()->check()) {
        $user = auth()->user();
        $notifications = $user->unreadNotifications;

        return response()->json([
            'logged_in' => true,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'unread_notifications' => $notifications->count(),
            'latest_notifications' => $notifications->take(3)->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Sin título',
                    'created_at' => $n->created_at,
                ];
            }),
        ]);
    } else {
        return response()->json(['logged_in' => false]);
    }
});
