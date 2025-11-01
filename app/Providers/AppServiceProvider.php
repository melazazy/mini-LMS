<?php

namespace App\Providers;

use App\Events\CourseCompleted;
use App\Events\EnrollmentCreated;
use App\Listeners\SendCourseCompletionNotification;
use App\Listeners\SendEnrollmentNotification;
use App\Listeners\SendWelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register morph map for polymorphic relationships
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'course' => \App\Models\Course::class,
            'lesson' => \App\Models\Lesson::class,
        ]);

        // Register event listeners
        Event::listen(
            Registered::class,
            SendWelcomeNotification::class,
        );

        Event::listen(
            EnrollmentCreated::class,
            SendEnrollmentNotification::class,
        );

        Event::listen(
            CourseCompleted::class,
            SendCourseCompletionNotification::class,
        );

        // CertificateIssued listener is auto-discovered by Laravel
        // Removed manual registration to prevent duplicate emails
    }
}
