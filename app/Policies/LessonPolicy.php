<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Lesson $lesson)
    {
        // Free preview lessons visible to everyone
        if ($lesson->is_free_preview && $lesson->is_published) {
            return true;
        }

        // Published lessons visible to enrolled students
        if ($lesson->is_published) {
            return $user->isStudent() && 
                   $user->enrolledCourses()->where('course_id', $lesson->course_id)->exists();
        }

        // Unpublished lessons only visible to creators and admins
        return $user->canManageContent() && 
               ($user->isAdmin() || $lesson->course->created_by === $user->id);
    }

    public function create(User $user)
    {
        return $user->canManageContent();
    }

    public function update(User $user, Lesson $lesson)
    {
        return $user->isAdmin() || $lesson->course->created_by === $user->id;
    }

    public function delete(User $user, Lesson $lesson)
    {
        return $user->isAdmin() || $lesson->course->created_by === $user->id;
    }

    public function watch(User $user, Lesson $lesson)
    {
        // Free preview lessons can be watched by anyone
        if ($lesson->is_free_preview && $lesson->is_published) {
            return true;
        }

        // Regular lessons require enrollment
        return $user->isStudent() && 
               $user->enrolledCourses()->where('course_id', $lesson->course_id)->exists();
    }
}