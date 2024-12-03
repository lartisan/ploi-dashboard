<?php

namespace Lartisan\PloiDashboard\Actions;

use Filament\Notifications\Notification;
use Lartisan\PloiDashboard\Concerns\Resolvable;

class NotificationMessage
{
    use Resolvable;

    public function send(string $type, string $message, ?string $body = null): void
    {
        $messageArray = json_decode($message, true);

        [$message, $errors] = $messageArray ? array_values($messageArray) : [$message, null];

        Notification::make()
            ->$type()
            ->title($message)
            ->when(
                value: $body || ($message === $errors),
                callback: fn ($notification) => $notification->body($body),
                default: fn ($notification) => $notification->body($errors)
            )
            ->send();
    }
}
