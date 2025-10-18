<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnrollmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->isAdmin() || $user->isInstructor();
    }

    public function view(User $user, Enrollment $enrollment)
    {
        return $user->isAdmin() || 
               $enrollment->user_id === $user->id ||
               $enrollment->course->created_by === $user->id;
    }

    public function create(User $user)
    {
        return $user->isStudent();
    }

    public function update(User $user, Enrollment $enrollment)
    {
        return $user->isAdmin() || $enrollment->user_id === $user->id;
    }

    public function delete(User $user, Enrollment $enrollment)
    {
        return $user->isAdmin() || $enrollment->user_id === $user->id;
    }
}