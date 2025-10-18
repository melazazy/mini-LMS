<?php

namespace App\Actions\Enrollment;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelEnrollmentAction
{
    public function execute(User $user, Enrollment $enrollment): bool
    {
        // Check if user owns the enrollment or is admin
        if ($enrollment->user_id !== $user->id && !$user->isAdmin()) {
            throw new \Exception('Unauthorized to cancel this enrollment.');
        }

        // Check if enrollment is active
        if ($enrollment->status !== 'active') {
            throw new \Exception('Enrollment is not active and cannot be cancelled.');
        }

        return DB::transaction(function () use ($enrollment, $user) {
            $enrollment->update(['status' => 'canceled']);

            Log::info('Enrollment cancelled', [
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
            ]);

            return true;
        });
    }
}
