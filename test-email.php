<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first();

if ($user) {
    echo "Testing email for: {$user->email}\n";
    
    try {
        $user->notify(new App\Notifications\WelcomeNotification());
        echo "✅ Email sent successfully!\n";
        echo "Check your inbox: {$user->email}\n";
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No users found in database\n";
}
