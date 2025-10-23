<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Notifications\CourseCompletionNotification;
use Illuminate\Support\Facades\Log;

class SendCourseCompletionNotification
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
    public function handle(CourseCompleted $event): void
    {
        $user = $event->user;
        $course = $event->course;

        try {
            // Send notification immediately (synchronous)
            $user->notify(new CourseCompletionNotification($course));

            Log::info('Course completion notification sent', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'course_title' => $course->title,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send course completion notification', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
