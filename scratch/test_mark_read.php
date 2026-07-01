<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('approved', true)->first();
if ($user) {
    echo "User found: " . $user->email . "\n";
    // 1. Create a notification if none exists
    if ($user->unreadNotifications->count() === 0) {
        $dummy = (object)['title' => 'Test Notification', 'message' => 'Hello World', 'detailed_message' => 'Hello World details'];
        $user->notify(new \App\Notifications\SystemAnnouncementNotification($dummy));
        echo "Created 1 notification.\n";
    }

    echo "Unread count before: " . $user->unreadNotifications->count() . "\n";

    // 2. Try marking all as read
    $user->unreadNotifications->markAsRead();
    echo "Called markAsRead() on collection.\n";

    // Reload user instance
    $user = $user->fresh();
    echo "Unread count after fresh(): " . $user->unreadNotifications->count() . "\n";
} else {
    echo "No approved user found.\n";
}
