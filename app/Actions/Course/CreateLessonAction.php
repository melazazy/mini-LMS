<?php

namespace App\Actions\Course;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateLessonAction
{
    public function execute(User $user, Course $course, array $lessonData): Lesson
    {
        if (!$user->canManageContent() || (!$user->isAdmin() && $course->created_by !== $user->id)) {
            throw new \Exception('User is not authorized to create lessons for this course.');
        }

        return DB::transaction(function () use ($user, $course, $lessonData) {
            // Get next order number
            $nextOrder = $course->lessons()->max('order') + 1;

            $lesson = Lesson::create([
                'course_id' => $course->id,
                'title' => $lessonData['title'],
                'video_url' => $lessonData['video_url'] ?? null,
                'hls_manifest_url' => $lessonData['hls_manifest_url'] ?? null,
                'duration_seconds' => $lessonData['duration_seconds'] ?? 0,
                'order' => $nextOrder,
                'is_free_preview' => $lessonData['is_free_preview'] ?? false,
                'resources' => $lessonData['resources'] ?? null,
                'is_published' => false,
            ]);

            // Create moderation review
            $lesson->moderationReview()->create([
                'state' => 'draft',
                'submitted_by' => $user->id,
            ]);

            Log::info('Lesson created', [
                'lesson_id' => $lesson->id,
                'title' => $lesson->title,
                'course_id' => $course->id,
                'created_by' => $user->id,
            ]);

            return $lesson;
        });
    }
}
