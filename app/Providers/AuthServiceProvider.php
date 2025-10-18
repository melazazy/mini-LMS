<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\User;
use App\Policies\CoursePolicy;
use App\Policies\LessonPolicy;
use App\Policies\EnrollmentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Course::class => CoursePolicy::class,
        Lesson::class => LessonPolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Define gates
        Gate::define('manage-content', function (User $user) {
            return $user->canManageContent();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('moderate-content', function (User $user) {
            return $user->isAdmin();
        });
    }
}