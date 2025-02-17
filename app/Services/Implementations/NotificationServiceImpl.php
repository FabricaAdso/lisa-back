<?php

namespace App\Services\Implementations;

use App\Events\NotificationEvent;
use App\Models\Notification;
use App\Models\User;

class NotificationServiceImpl
{
    public function send(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        array $data = []
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data
        ]);

        event(new NotificationEvent($notification));

        return $notification;
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    public function getUnreadCount(User $user): int
    {

        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function getUserNotifications(User $user, bool $onlyUnread = false)
    {
        $query = Notification::where('user_id', $user->id);

        if ($onlyUnread) {
            $query->whereNull('read_at');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}