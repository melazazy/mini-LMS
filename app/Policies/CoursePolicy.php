<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true; // Everyone can view courses
    }

    public function view(User $user, Course $course)
    {
        // Published courses are visible to everyone
        if ($course->is_published) {
            return true;
        }

        // Unpublished courses only visible to creators and admins
        return $user->canManageContent() && 
               ($user->isAdmin() || $course->created_by === $user->id);
    }

    public function create(User $user)
    {
        return $user->canManageContent();
    }

    public function update(User $user, Course $course)
    {
        return $user->isAdmin() || $course->created_by === $user->id;
    }

    public function delete(User $user, Course $course)
    {
        return $user->isAdmin() || $course->created_by === $user->id;
    }

    public function enroll(User $user, Course $course)
    {
        // Students can enroll in published, non-deleted courses
        return $user->isStudent() && $course->is_published && !$course->trashed();
    }
    
    /**
     * Determine whether the user can view trashed courses.
     */
    public function viewTrashed(User $user, Course $course)
    {
        // Only admins and course creators can view trashed courses
        return $user->isAdmin() || $course->created_by === $user->id;
    }
    
    /**
     * Determine whether the user can view unpublished courses.
     */
    public function viewUnpublished(User $user, Course $course)
    {
        // Only admins and course creators can view unpublished courses
        return $user->isAdmin() || $course->created_by === $user->id;
    }
    
    /**
     * Determine whether the user can restore the course.
     */
    public function restore(User $user, Course $course)
    {
        // Only admins and course creators can restore courses
        return $user->isAdmin() || $course->created_by === $user->id;
    }
    
    /**
     * Determine whether the user can permanently delete the course.
     */
    public function forceDelete(User $user, Course $course)
    {
        // Only admins can force delete courses
        return $user->isAdmin();
    }

    public function manage(User $user, Course $course)
    {
        return $user->isAdmin() || $course->created_by === $user->id;
    }
}