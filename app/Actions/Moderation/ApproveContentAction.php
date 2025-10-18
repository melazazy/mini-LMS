<?php

namespace App\Actions\Moderation;

use App\Models\ModerationReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveContentAction
{
    public function execute(User $user, ModerationReview $review, string $notes = null): ModerationReview
    {
        if (!$user->isAdmin()) {
            throw new \Exception('Only admins can approve content.');
        }

        if ($review->state !== 'pending') {
            throw new \Exception('Only pending reviews can be approved.');
        }

        return DB::transaction(function () use ($user, $review, $notes) {
            $review->update([
                'state' => 'approved',
                'reviewer_id' => $user->id,
                'notes' => $notes,
            ]);

            // Publish the content
            $subject = $review->subject;
            if ($subject) {
                $subject->update(['is_published' => true]);
            }

            Log::info('Content approved', [
                'review_id' => $review->id,
                'subject_type' => $review->subject_type,
                'subject_id' => $review->subject_id,
                'approved_by' => $user->id,
            ]);

            return $review;
        });
    }
}
