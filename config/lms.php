<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lesson Completion Threshold
    |--------------------------------------------------------------------------
    |
    | The percentage of a lesson that must be watched before it is considered
    | completed. This value should be between 0 and 100.
    |
    */
    'lesson_completion_threshold' => env('LMS_LESSON_COMPLETION_THRESHOLD', 90),

    /*
    |--------------------------------------------------------------------------
    | Progress Update Interval
    |--------------------------------------------------------------------------
    |
    | The interval (in seconds) at which the video player will update the
    | lesson progress on the server.
    |
    */
    'progress_update_interval' => env('LMS_PROGRESS_UPDATE_INTERVAL', 5),
];
