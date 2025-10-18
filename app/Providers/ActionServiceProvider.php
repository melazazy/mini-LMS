<?php

namespace App\Providers;

use App\Actions\Enrollment\EnrollInCourseAction;
use App\Actions\Enrollment\EnrollInFreeCourseAction;
use App\Actions\Enrollment\CancelEnrollmentAction;
use App\Actions\Progress\UpdateLessonProgressAction;
use App\Actions\Progress\GetUserProgressAction;
use App\Actions\Course\CreateCourseAction;
use App\Actions\Course\PublishCourseAction;
use App\Actions\Course\CreateLessonAction;
use App\Actions\Moderation\SubmitForReviewAction;
use App\Actions\Moderation\ApproveContentAction;
use App\Actions\Moderation\RejectContentAction;
use Illuminate\Support\ServiceProvider;

class ActionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(EnrollInCourseAction::class);
        $this->app->singleton(EnrollInFreeCourseAction::class);
        $this->app->singleton(CancelEnrollmentAction::class);
        $this->app->singleton(UpdateLessonProgressAction::class);
        $this->app->singleton(GetUserProgressAction::class);
        $this->app->singleton(CreateCourseAction::class);
        $this->app->singleton(PublishCourseAction::class);
        $this->app->singleton(CreateLessonAction::class);
        $this->app->singleton(SubmitForReviewAction::class);
        $this->app->singleton(ApproveContentAction::class);
        $this->app->singleton(RejectContentAction::class);
    }

    public function boot()
    {
        //
    }
}
