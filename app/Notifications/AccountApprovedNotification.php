<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $data;

    public function __construct($data = [])
    {
        $this->data = is_array($data) ? (object)$data : $data;
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
            'title' => $this->data->title ?? 'Account Approved',
            'message' => $this->data->message ?? 'Your account has been approved by the administrator.',
            'detailed_message' => $this->data->detailed_message ?? 'You can now access all features available to your role.',
            'action_url' => null,
            'type' => 'account_approved',
        ];
    }
}
