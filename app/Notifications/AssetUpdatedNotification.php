<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssetUpdatedNotification extends Notification
{
    use Queueable;
    public $data;

    public function __construct($data)
    {
        $this->data = is_array($data) ? (object)$data : $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data->title ?? 'Asset Updated',
            'message' => $this->data->message ?? 'An asset has been updated.',
            'detailed_message' => $this->data->detailed_message ?? ($this->data->description ?? 'No details available.'),
            'action_url' => null,
            'type' => 'asset_updated',
        ];
    }
}
