<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssetAddedNotification extends Notification
{
    use Queueable;
    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->asset->title ?? 'Asset Added',
            'message' => $this->asset->message ?? 'A new asset has been added.',
            'detailed_message' => $this->asset->detailed_message ?? ($this->asset->description ?? 'No details available.'),
            'action_url' => null,
            'type' => 'asset_added',
        ];
    }
}
