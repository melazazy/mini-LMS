<?php

namespace App\Actions\Course;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateCourseAction
{
    public function execute(User $user, array $courseData): Course
    {
        if (!$user->canManageContent()) {
            throw new \Exception('User is not authorized to create courses.');
        }

        return DB::transaction(function () use ($user, $courseData) {
            $course = Course::create([
                'title' => $courseData['title'],
                'description' => $courseData['description'],
                'level' => $courseData['level'],
                'price' => $courseData['price'] ?? null,
                'currency' => $courseData['currency'] ?? 'USD',
                'thumbnail_url' => $courseData['thumbnail_url'] ?? null,
                'free_lesson_count' => $courseData['free_lesson_count'] ?? 0,
                'created_by' => $user->id,
                'is_published' => false,
            ]);

            // Create moderation review
            $course->moderationReview()->create([
                'state' => 'draft',
                'submitted_by' => $user->id,
            ]);

            Log::info('Course created', [
                'course_id' => $course->id,
                'title' => $course->title,
                'created_by' => $user->id,
            ]);

            return $course;
        });
    }
}
