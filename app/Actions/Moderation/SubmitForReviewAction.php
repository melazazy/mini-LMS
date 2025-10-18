<?php

namespace App\Actions\Moderation;

use App\Models\ModerationReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmitForReviewAction
{
    public function execute(User $user, $subject, string $notes = null): ModerationReview
    {
        if (!$user->canManageContent()) {
            throw new \Exception('User is not authorized to submit content for review.');
        }

        $subjectType = $this->getSubjectType($subject);
        $subjectId = $subject->id;

        return DB::transaction(function () use ($user, $subjectType, $subjectId, $notes) {
            $review = ModerationReview::updateOrCreate(
                [
                    'subject_type' => $subjectType,
                    'subject_id' => $subjectId,
                ],
                [
                    'state' => 'pending',
                    'submitted_by' => $user->id,
                    'notes' => $notes,
                ]
            );

            Log::info('Content submitted for review', [
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'submitted_by' => $user->id,
                'review_id' => $review->id,
            ]);

            return $review;
        });
    }

    private function getSubjectType($subject): string
    {
        return get_class($subject);
    }
}
