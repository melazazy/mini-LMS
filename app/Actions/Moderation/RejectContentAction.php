<?php

namespace App\Actions\Moderation;

use App\Models\ModerationReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RejectContentAction
{
    public function execute(User $user, ModerationReview $review, string $reason): ModerationReview
    {
        if (!$user->isAdmin()) {
            throw new \Exception('Only admins can reject content.');
        }

        if ($review->state !== 'pending') {
            throw new \Exception('Only pending reviews can be rejected.');
        }

        return DB::transaction(function () use ($user, $review, $reason) {
            $review->update([
                'state' => 'rejected',
                'reviewer_id' => $user->id,
                'notes' => $reason,
            ]);

            Log::info('Content rejected', [
                'review_id' => $review->id,
                'subject_type' => $review->subject_type,
                'subject_id' => $review->subject_id,
                'rejected_by' => $user->id,
                'reason' => $reason,
            ]);

            return $review;
        });
    }
}
