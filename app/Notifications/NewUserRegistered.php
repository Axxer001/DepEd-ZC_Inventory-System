<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data->title ?? 'New User Registration',
            'message' => $this->data->message ?? 'A new user has registered.',
            'detailed_message' => $this->data->detailed_message ?? 'Please review the new user account.',
            'action_url' => null,
            'type' => 'new_user_registered',
        ];
    }
}
