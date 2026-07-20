<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssetTransferNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $data;

    public function __construct($data, $actionText = null)
    {
        $this->data = is_array($data) ? (object)$data : $data;
        if ($actionText && !isset($this->data->message)) {
            $this->data->message = $actionText;
        }
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data->title ?? 'Asset Transfer',
            'message' => $this->data->message ?? 'An asset transfer event occurred.',
            'detailed_message' => $this->data->detailed_message ?? ($this->data->article ?? 'No details available.'),
            'action_url' => null,
            'type' => 'asset_transfer',
        ];
    }
}
