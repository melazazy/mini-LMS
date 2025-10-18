<?php

namespace App\Actions\Course;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishCourseAction
{
    public function execute(User $user, Course $course): Course
    {
        if (!$user->isAdmin() && $course->created_by !== $user->id) {
            throw new \Exception('User is not authorized to publish this course.');
        }

        return DB::transaction(function () use ($user, $course) {
            $course->update([
                'is_published' => true,
                'published_at' => now(),
            ]);

            // Update moderation review
            if ($course->moderationReview) {
                $course->moderationReview->update([
                    'state' => 'approved',
                    'reviewer_id' => $user->id,
                ]);
            }

            Log::info('Course published', [
                'course_id' => $course->id,
                'title' => $course->title,
                'published_by' => $user->id,
            ]);

            return $course;
        });
    }
}
