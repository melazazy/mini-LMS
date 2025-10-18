<?php

namespace App\Actions\Progress;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Collection;

class GetUserProgressAction
{
    public function execute(User $user, Course $course): array
    {
        $enrollment = $user->enrolledCourses()->where('course_id', $course->id)->first();
        
        if (!$enrollment) {
            throw new \Exception('User is not enrolled in this course.');
        }

        $lessons = $course->publishedLessons()->get();
        $progress = $user->lessonProgress()
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->get()
            ->keyBy('lesson_id');

        $courseProgress = [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'total_lessons' => $lessons->count(),
            'completed_lessons' => 0,
            'in_progress_lessons' => 0,
            'not_started_lessons' => 0,
            'overall_percentage' => 0,
            'lessons' => [],
        ];

        foreach ($lessons as $lesson) {
            $lessonProgress = $progress->get($lesson->id);
            $watchedPercentage = $lessonProgress ? $lessonProgress->watched_percentage : 0;
            $isCompleted = $watchedPercentage >= 90;
            $isInProgress = $watchedPercentage > 0 && $watchedPercentage < 90;

            if ($isCompleted) {
                $courseProgress['completed_lessons']++;
            } elseif ($isInProgress) {
                $courseProgress['in_progress_lessons']++;
            } else {
                $courseProgress['not_started_lessons']++;
            }

            $courseProgress['lessons'][] = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'order' => $lesson->order,
                'duration' => $lesson->formatted_duration,
                'watched_percentage' => $watchedPercentage,
                'last_position' => $lessonProgress ? $lessonProgress->formatted_position : '0:00',
                'is_completed' => $isCompleted,
                'is_in_progress' => $isInProgress,
                'is_free_preview' => $lesson->is_free_preview,
            ];
        }

        $courseProgress['overall_percentage'] = $courseProgress['total_lessons'] > 0 
            ? round(($courseProgress['completed_lessons'] / $courseProgress['total_lessons']) * 100, 2)
            : 0;

        return $courseProgress;
    }
}
