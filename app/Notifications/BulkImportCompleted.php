<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BulkImportCompleted extends Notification
{
    use Queueable;

    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Bulk Import Completed',
            'message' => "Import finished. {$this->details}",
            'action_url' => route('assets.reports'),
            'icon' => 'fas fa-file-import text-info',
            'type' => 'bulk_import',
        ];
    }
}
