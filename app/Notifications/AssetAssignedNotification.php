<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssetAssignedNotification extends Notification
{
    use Queueable;

    protected $asset;

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
            'title' => 'New Asset Assigned',
            'message' => 'You have been assigned a new asset: ' . ($this->asset->article ?? 'Unknown Item'),
            'action_url' => route('assets.profile', ['id' => $this->asset->id]),
            'icon' => 'fas fa-box-open text-success',
            'type' => 'asset_assigned',
        ];
    }
}
