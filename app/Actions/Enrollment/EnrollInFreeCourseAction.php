<?php

namespace App\Actions\Enrollment;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollInFreeCourseAction
{
    public function execute(User $user, Course $course): Enrollment
    {
        // Validate course is free
        if (!$course->isFree()) {
            throw new \Exception('Course is not free. Use paid enrollment action.');
        }

        // Check if user is already enrolled
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            throw new \Exception('User is already enrolled in this course.');
        }

        // Check if course is published
        if (!$course->is_published) {
            throw new \Exception('Course is not available for enrollment.');
        }

        return DB::transaction(function () use ($user, $course) {
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => 'active',
                'paid_amount' => null,
                'currency' => 'USD', // Default currency even for free courses
                'payment_id' => null,
            ]);

            Log::info('User enrolled in free course', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
            ]);

            return $enrollment;
        });
    }
}
