<?php

namespace App\Listeners;

use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class SendWelcomeNotification
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        try {
            // Send welcome notification immediately (synchronous)
            $user->notify(new WelcomeNotification());

            Log::info('Welcome notification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
