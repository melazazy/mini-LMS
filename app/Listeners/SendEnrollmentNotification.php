<?php

namespace App\Listeners;

use App\Events\EnrollmentCreated;
use App\Notifications\EnrollmentNotification;
use Illuminate\Support\Facades\Log;

class SendEnrollmentNotification
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
    public function handle(EnrollmentCreated $event): void
    {
        $enrollment = $event->enrollment;
        $user = $enrollment->user;

        try {
            // Send notification immediately (synchronous)
            $user->notify(new EnrollmentNotification($enrollment));

            Log::info('Enrollment notification sent', [
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send enrollment notification', [
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to allow queue retry
            throw $e;
        }
    }
}
