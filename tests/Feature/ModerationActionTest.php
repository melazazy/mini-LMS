<?php

namespace Tests\Feature;

use App\Actions\Moderation\SubmitForReviewAction;
use App\Actions\Moderation\ApproveContentAction;
use App\Actions\Moderation\RejectContentAction;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\ModerationReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_submit_course_for_review()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $action = app(SubmitForReviewAction::class);
        $review = $action->execute($instructor, $course, 'Ready for review');

        $this->assertInstanceOf(ModerationReview::class, $review);
        $this->assertEquals('pending', $review->state);
        $this->assertEquals('App\\Models\\Course', $review->subject_type);
        $this->assertEquals($course->id, $review->subject_id);
        $this->assertEquals($instructor->id, $review->submitted_by);
        $this->assertEquals('Ready for review', $review->notes);
        
        $this->assertDatabaseHas('moderation_reviews', [
            'subject_type' => 'App\\Models\\Course',
            'subject_id' => $course->id,
            'state' => 'pending',
            'submitted_by' => $instructor->id,
        ]);
    }

    public function test_instructor_can_submit_lesson_for_review()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $action = app(SubmitForReviewAction::class);
        $review = $action->execute($instructor, $lesson, 'Lesson ready for approval');

        $this->assertInstanceOf(ModerationReview::class, $review);
        $this->assertEquals('pending', $review->state);
        $this->assertEquals('App\\Models\\Lesson', $review->subject_type);
        $this->assertEquals($lesson->id, $review->subject_id);
    }

    public function test_student_cannot_submit_content_for_review()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        $action = app(SubmitForReviewAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not authorized to submit content for review.');
        
        $action->execute($student, $course);
    }

    public function test_admin_can_approve_content()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'created_by' => $instructor->id,
            'is_published' => false,
        ]);

        $review = ModerationReview::factory()->create([
            'subject_type' => 'App\\Models\\Course',
            'subject_id' => $course->id,
            'state' => 'pending',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(ApproveContentAction::class);
        $approvedReview = $action->execute($admin, $review, 'Content approved');

        $this->assertEquals('approved', $approvedReview->state);
        $this->assertEquals($admin->id, $approvedReview->reviewer_id);
        $this->assertEquals('Content approved', $approvedReview->notes);
        
        // Check that course is now published
        $course->refresh();
        $this->assertTrue($course->is_published);
        
        $this->assertDatabaseHas('moderation_reviews', [
            'id' => $review->id,
            'state' => 'approved',
            'reviewer_id' => $admin->id,
        ]);
    }

    public function test_instructor_cannot_approve_content()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $review = ModerationReview::factory()->create([
            'subject_type' => 'App\\Models\\Course',
            'subject_id' => $course->id,
            'state' => 'pending',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(ApproveContentAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only admins can approve content.');
        
        $action->execute($instructor, $review);
    }

    public function test_cannot_approve_non_pending_review()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $review = ModerationReview::factory()->create([
            'subject_type' => 'App\\Models\\Course',
            'subject_id' => $course->id,
            'state' => 'approved',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(ApproveContentAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only pending reviews can be approved.');
        
        $action->execute($admin, $review);
    }

    public function test_admin_can_reject_content()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $review = ModerationReview::factory()->create([
            'subject_type' => 'App\\Models\\Course',
            'subject_id' => $course->id,
            'state' => 'pending',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(RejectContentAction::class);
        $rejectedReview = $action->execute($admin, $review, 'Content needs improvement');

        $this->assertEquals('rejected', $rejectedReview->state);
        $this->assertEquals($admin->id, $rejectedReview->reviewer_id);
        $this->assertEquals('Content needs improvement', $rejectedReview->notes);
        
        $this->assertDatabaseHas('moderation_reviews', [
            'id' => $review->id,
            'state' => 'rejected',
            'reviewer_id' => $admin->id,
        ]);
    }

    public function test_instructor_cannot_reject_content()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $review = ModerationReview::factory()->create([
            'subject_type' => 'App\\Models\\Course',
            'subject_id' => $course->id,
            'state' => 'pending',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(RejectContentAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only admins can reject content.');
        
        $action->execute($instructor, $review, 'Rejection reason');
    }

    public function test_cannot_reject_non_pending_review()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $review = ModerationReview::factory()->create([
            'subject_type' => 'App\\Models\\Course',
            'subject_id' => $course->id,
            'state' => 'rejected',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(RejectContentAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only pending reviews can be rejected.');
        
        $action->execute($admin, $review, 'Rejection reason');
    }

    public function test_submit_for_review_updates_existing_review()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        // Create initial review
        $initialReview = ModerationReview::factory()->create([
            'subject_type' => 'App\\Models\\Course',
            'subject_id' => $course->id,
            'state' => 'draft',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(SubmitForReviewAction::class);
        $updatedReview = $action->execute($instructor, $course, 'Updated submission');

        $this->assertEquals($initialReview->id, $updatedReview->id);
        $this->assertEquals('pending', $updatedReview->state);
        $this->assertEquals('Updated submission', $updatedReview->notes);
    }
}