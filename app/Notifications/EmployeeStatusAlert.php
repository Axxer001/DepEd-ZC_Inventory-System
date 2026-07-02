<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmployeeStatusAlert extends Notification
{
    use Queueable;

    protected $employee;
    protected $status;
    protected $assetCount;

    public function __construct($employee, $status, $assetCount)
    {
        $this->employee = $employee;
        $this->status = $status;
        $this->assetCount = $assetCount;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Employee Status Changed',
            'message' => "{$this->employee->first_name} {$this->employee->last_name} status is now {$this->status}. They have {$this->assetCount} active assets.",
            'action_url' => route('custodians.profile', ['id' => $this->employee->id]),
            'icon' => 'fas fa-exclamation-triangle text-warning',
            'type' => 'employee_status',
        ];
    }
}
