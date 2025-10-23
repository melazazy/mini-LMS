<?php

namespace App\Actions\Progress;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateLessonProgressAction
{
    public function execute(
        User $user, 
        Lesson $lesson, 
        int $watchedPercentage, 
        int $lastPositionSeconds
    ): LessonProgress {
        // Validate user can watch this lesson
        if (!$this->canUserWatchLesson($user, $lesson)) {
            throw new \Exception('User is not authorized to watch this lesson.');
        }

        // Validate percentage
        if ($watchedPercentage < 0 || $watchedPercentage > 100) {
            throw new \Exception('Invalid watched percentage.');
        }

        return DB::transaction(function () use ($user, $lesson, $watchedPercentage, $lastPositionSeconds) {
            $existingProgress = LessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();
            
            $wasAlreadyCompleted = $existingProgress && $existingProgress->watched_percentage >= 90;
            
            // Determine the percentage to save
            // If lesson is already completed, don't reduce the percentage
            $percentageToSave = $watchedPercentage;
            if ($existingProgress && $existingProgress->watched_percentage >= 90) {
                // Keep the higher percentage (don't let it go down from 100%)
                $percentageToSave = max($existingProgress->watched_percentage, $watchedPercentage);
            }
            
            $progress = LessonProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'watched_percentage' => $percentageToSave,
                    'last_position_seconds' => $lastPositionSeconds,
                    'last_watched_at' => now(),
                ]
            );

            // Check if lesson is completed (90% threshold)
            if ($watchedPercentage >= 90 && !$wasAlreadyCompleted) {
                $this->handleLessonCompletion($user, $lesson);
            }

            Log::debug('Lesson progress updated', [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'percentage' => $percentageToSave,
                'position' => $lastPositionSeconds,
                'was_completed' => $wasAlreadyCompleted,
            ]);

            return $progress;
        });
    }

    private function canUserWatchLesson(User $user, Lesson $lesson): bool
    {
        // Free preview lessons can be watched by anyone
        if ($lesson->is_free_preview && $lesson->is_published) {
            return true;
        }

        // Regular lessons require enrollment
        return $user->isStudent() && 
               $user->enrolledCourses()->where('course_id', $lesson->course_id)->exists();
    }

    private function handleLessonCompletion(User $user, Lesson $lesson): void
    {
        Log::info('Lesson completed', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->course_id,
        ]);

        // Check if course is completed
        $this->checkCourseCompletion($user, $lesson->course);
    }

    private function checkCourseCompletion(User $user, $course): void
    {
        $totalLessons = $course->publishedLessons()->count();
        $completedLessons = $user->lessonProgress()
            ->whereHas('lesson', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->where('watched_percentage', '>=', 90)
            ->count();

        if ($totalLessons > 0 && $completedLessons >= $totalLessons) {
            $this->completeCourse($user, $course);
        }
    }

    private function completeCourse(User $user, $course): void
    {
        // Check if already completed
        if ($user->courseCompletions()->where('course_id', $course->id)->exists()) {
            return;
        }

        DB::transaction(function () use ($user, $course) {
            $user->courseCompletions()->create([
                'course_id' => $course->id,
                'completed_at' => now(),
            ]);

            Log::info('Course completed', [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);

            // Dispatch course completion event
            event(new \App\Events\CourseCompleted($user, $course));
        });
    }
}
