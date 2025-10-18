<?php

namespace App\Actions\Enrollment;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollInCourseAction
{
    public function execute(User $user, Course $course, array $paymentData = null): Enrollment
    {
        // Check if user is already enrolled
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            throw new \Exception('User is already enrolled in this course.');
        }

        // Check if course is published
        if (!$course->is_published) {
            throw new \Exception('Course is not available for enrollment.');
        }

        return DB::transaction(function () use ($user, $course, $paymentData) {
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => 'active',
                'paid_amount' => $course->price,
                'currency' => $course->currency ?? 'USD',
                'payment_id' => $paymentData['payment_id'] ?? null,
            ]);

            Log::info('User enrolled in course', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
            ]);

            return $enrollment;
        });
    }
}
