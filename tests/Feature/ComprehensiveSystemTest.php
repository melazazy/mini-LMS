<?php

namespace Tests\Feature;

use App\Actions\Course\CreateCourseAction;
use App\Actions\Course\CreateLessonAction;
use App\Actions\Course\PublishCourseAction;
use App\Actions\Enrollment\EnrollInFreeCourseAction;
use App\Actions\Enrollment\EnrollInCourseAction;
use App\Actions\Enrollment\CancelEnrollmentAction;
use App\Actions\Progress\UpdateLessonProgressAction;
use App\Actions\Progress\GetUserProgressAction;
use App\Actions\Moderation\SubmitForReviewAction;
use App\Actions\Moderation\ApproveContentAction;
use App\Actions\Moderation\RejectContentAction;
use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\ModerationReview;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ComprehensiveSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Step 2: Test Database Models and Relations
     */
    public function test_step_2_user_model_exists_with_correct_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'student',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'student',
        ]);

        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->isInstructor());
        $this->assertFalse($user->isAdmin());
    }

    public function test_step_2_course_model_with_relationships(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        
        $course = Course::create([
            'title' => 'Test Course',
            'description' => 'Test Description',
            'level' => 'beginner',
            'price' => 99.99,
            'currency' => 'USD',
            'created_by' => $instructor->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'Test Course',
            'level' => 'beginner',
            'price' => 99.99,
        ]);

        // Test relationships
        $this->assertEquals($instructor->id, $course->creator->id);
        $this->assertInstanceOf(User::class, $course->creator);
    }

    public function test_step_2_lesson_model_with_course_relationship(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);
        
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Introduction Lesson',
            'video_url' => 'https://example.com/video.mp4',
            'duration_seconds' => 600,
            'order' => 1,
            'is_published' => true,
            'is_free_preview' => false,
        ]);

        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'title' => 'Introduction Lesson',
            'duration_seconds' => 600,
        ]);

        $this->assertEquals($course->id, $lesson->course->id);
        $this->assertTrue($course->lessons->contains($lesson));
    }

    public function test_step_2_enrollment_model_and_pivot_relationship(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'paid_amount' => 99.99,
            'currency' => 'USD',
            'payment_id' => 'test_payment_123',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->assertTrue($student->enrolledCourses->contains($course));
        $this->assertTrue($course->students->contains($student));
    }

    public function test_step_2_lesson_progress_tracking(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'is_published' => true]);

        $progress = LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 75,
            'last_position_seconds' => 450,
            'last_watched_at' => now(),
        ]);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 75,
        ]);

        $this->assertTrue($progress->isInProgress());
        $this->assertFalse($progress->isCompleted());
    }

    public function test_step_2_course_completion_tracking(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);

        $completion = CourseCompletion::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => now(),
        ]);

        $this->assertDatabaseHas('course_completions', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->assertTrue($student->completedCourses->contains($course));
    }

    public function test_step_2_moderation_review_polymorphic_relationship(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $review = ModerationReview::create([
            'subject_type' => 'course',
            'subject_id' => $course->id,
            'state' => 'pending',
            'submitted_by' => $instructor->id,
            'reviewer_id' => $admin->id,
            'notes' => 'Please review this course',
        ]);

        $this->assertDatabaseHas('moderation_reviews', [
            'subject_type' => 'course',
            'subject_id' => $course->id,
            'state' => 'pending',
        ]);

        $this->assertTrue($review->isPending());
    }

    public function test_step_2_notification_model(): void
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'course_completed',
            'data' => ['course_id' => 1, 'course_title' => 'Test Course'],
            'read_at' => null,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'course_completed',
        ]);

        $this->assertTrue($notification->isUnread());
        
        $notification->markAsRead();
        $this->assertTrue($notification->fresh()->isRead());
    }

    /**
     * Step 3: Test Authentication and Authorization
     */
    public function test_step_3_user_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@example.com',
            'role' => 'student',
        ]);
    }

    public function test_step_3_user_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_step_3_user_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_step_3_course_policy_view_published_course(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);

        $this->assertTrue($student->can('view', $course));
    }

    public function test_step_3_course_policy_cannot_view_unpublished_course(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'is_published' => false,
            'created_by' => $instructor->id,
        ]);

        $this->assertFalse($student->can('view', $course));
        $this->assertTrue($instructor->can('view', $course));
    }

    public function test_step_3_course_policy_instructor_can_create(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $this->assertTrue($instructor->can('create', Course::class));
        $this->assertFalse($student->can('create', Course::class));
    }

    public function test_step_3_lesson_policy_enrolled_student_can_watch(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        // Enroll student
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->assertTrue($student->can('watch', $lesson));
    }

    public function test_step_3_lesson_policy_free_preview_accessible_to_all(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
        ]);

        $this->assertTrue($student->can('watch', $lesson));
    }

    /**
     * Step 4: Test Business Logic Actions
     */
    public function test_step_4_enroll_in_free_course_action(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create([
            'price' => null,
            'is_published' => true,
        ]);

        $action = app(EnrollInFreeCourseAction::class);
        $enrollment = $action->execute($student, $course);

        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertEquals('active', $enrollment->status);
        $this->assertNull($enrollment->paid_amount);
        
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }

    public function test_step_4_enroll_in_paid_course_action(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create([
            'price' => 149.99,
            'is_published' => true,
        ]);

        $action = app(EnrollInCourseAction::class);
        $enrollment = $action->execute($student, $course, [
            'payment_id' => 'stripe_pi_123456',
        ]);

        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertEquals(149.99, $enrollment->paid_amount);
        $this->assertEquals('stripe_pi_123456', $enrollment->payment_id);
    }

    public function test_step_4_cannot_enroll_twice_in_same_course(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create([
            'price' => null,
            'is_published' => true,
        ]);

        $action = app(EnrollInFreeCourseAction::class);
        $action->execute($student, $course);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is already enrolled in this course.');
        
        $action->execute($student, $course);
    }

    public function test_step_4_cancel_enrollment_action(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $action = app(CancelEnrollmentAction::class);
        $result = $action->execute($student, $enrollment);

        $this->assertTrue($result);
        $this->assertEquals('canceled', $enrollment->fresh()->status);
    }

    public function test_step_4_update_lesson_progress_action(): void
    {
        Event::fake();
        
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'duration_seconds' => 600,
        ]);

        // Enroll student
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $action = app(UpdateLessonProgressAction::class);
        $progress = $action->execute($student, $lesson, 50, 300);

        $this->assertInstanceOf(LessonProgress::class, $progress);
        $this->assertEquals(50, $progress->watched_percentage);
        $this->assertEquals(300, $progress->last_position_seconds);
    }

    public function test_step_4_lesson_completion_triggers_at_90_percent(): void
    {
        Event::fake();
        
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $action = app(UpdateLessonProgressAction::class);
        $progress = $action->execute($student, $lesson, 95, 570);

        $this->assertTrue($progress->isCompleted());
        $this->assertGreaterThanOrEqual(90, $progress->watched_percentage);
    }

    public function test_step_4_get_user_progress_action(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        
        $lesson1 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'order' => 1,
        ]);
        
        $lesson2 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'order' => 2,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson1->id,
            'watched_percentage' => 100,
            'last_position_seconds' => 600,
            'last_watched_at' => now(),
        ]);

        $action = app(GetUserProgressAction::class);
        $progress = $action->execute($student, $course);

        $this->assertIsArray($progress);
        $this->assertEquals($course->id, $progress['course_id']);
        $this->assertEquals(2, $progress['total_lessons']);
        $this->assertEquals(1, $progress['completed_lessons']);
        $this->assertEquals(50, $progress['overall_percentage']);
    }

    public function test_step_4_create_course_action(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);

        $action = app(CreateCourseAction::class);
        $course = $action->execute($instructor, [
            'title' => 'New Course',
            'description' => 'Course description',
            'level' => 'intermediate',
            'price' => 199.99,
            'currency' => 'USD',
            'free_lesson_count' => 2,
        ]);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('New Course', $course->title);
        $this->assertFalse($course->is_published);
        
        $this->assertDatabaseHas('courses', [
            'title' => 'New Course',
            'created_by' => $instructor->id,
        ]);
    }

    public function test_step_4_create_lesson_action(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $action = app(CreateLessonAction::class);
        $lesson = $action->execute($instructor, $course, [
            'title' => 'Introduction to Laravel',
            'video_url' => 'https://example.com/video.mp4',
            'duration_seconds' => 1200,
            'is_free_preview' => true,
        ]);

        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertEquals('Introduction to Laravel', $lesson->title);
        $this->assertTrue($lesson->is_free_preview);
        
        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'title' => 'Introduction to Laravel',
        ]);
    }

    public function test_step_4_publish_course_action(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'created_by' => $instructor->id,
            'is_published' => false,
        ]);

        $action = app(PublishCourseAction::class);
        $publishedCourse = $action->execute($instructor, $course);

        $this->assertTrue($publishedCourse->is_published);
        $this->assertNotNull($publishedCourse->published_at);
    }

    public function test_step_4_submit_for_review_action(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $action = app(SubmitForReviewAction::class);
        $review = $action->execute($instructor, $course, 'Please review my course');

        $this->assertInstanceOf(ModerationReview::class, $review);
        $this->assertEquals('pending', $review->state);
        $this->assertEquals('Please review my course', $review->notes);
    }

    public function test_step_4_approve_content_action(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create([
            'created_by' => $instructor->id,
            'is_published' => false,
        ]);

        $review = ModerationReview::create([
            'subject_type' => 'course',
            'subject_id' => $course->id,
            'state' => 'pending',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(ApproveContentAction::class);
        $approvedReview = $action->execute($admin, $review, 'Looks good!');

        $this->assertEquals('approved', $approvedReview->state);
        $this->assertEquals($admin->id, $approvedReview->reviewer_id);
        $this->assertTrue($course->fresh()->is_published);
    }

    public function test_step_4_reject_content_action(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);

        $review = ModerationReview::create([
            'subject_type' => 'course',
            'subject_id' => $course->id,
            'state' => 'pending',
            'submitted_by' => $instructor->id,
        ]);

        $action = app(RejectContentAction::class);
        $rejectedReview = $action->execute($admin, $review, 'Needs improvement');

        $this->assertEquals('rejected', $rejectedReview->state);
        $this->assertEquals('Needs improvement', $rejectedReview->notes);
    }

    /**
     * Integration Test: Complete User Journey
     */
    public function test_complete_user_journey_from_registration_to_course_completion(): void
    {
        Event::fake();
        
        // 1. Create instructor and course
        $instructor = User::factory()->create(['role' => 'instructor']);
        $createCourseAction = app(CreateCourseAction::class);
        
        $course = $createCourseAction->execute($instructor, [
            'title' => 'Complete Laravel Course',
            'description' => 'Learn Laravel from scratch',
            'level' => 'beginner',
            'price' => null,
            'free_lesson_count' => 1,
        ]);

        // 2. Create lessons
        $createLessonAction = app(CreateLessonAction::class);
        
        $lesson1 = $createLessonAction->execute($instructor, $course, [
            'title' => 'Lesson 1',
            'video_url' => 'https://example.com/lesson1.mp4',
            'duration_seconds' => 600,
            'is_free_preview' => true,
        ]);

        $lesson2 = $createLessonAction->execute($instructor, $course, [
            'title' => 'Lesson 2',
            'video_url' => 'https://example.com/lesson2.mp4',
            'duration_seconds' => 900,
            'is_free_preview' => false,
        ]);

        // 3. Publish course
        $publishAction = app(PublishCourseAction::class);
        $publishAction->execute($instructor, $course);

        // Publish lessons manually
        $lesson1->update(['is_published' => true]);
        $lesson2->update(['is_published' => true]);

        // 4. Student registers and enrolls
        $student = User::factory()->create(['role' => 'student']);
        
        $enrollAction = app(EnrollInFreeCourseAction::class);
        $enrollment = $enrollAction->execute($student, $course);

        $this->assertEquals('active', $enrollment->status);

        // 5. Student watches lessons
        $progressAction = app(UpdateLessonProgressAction::class);
        
        $progressAction->execute($student, $lesson1, 100, 600);
        $progressAction->execute($student, $lesson2, 95, 855);

        // 6. Verify progress
        $getProgressAction = app(GetUserProgressAction::class);
        $progress = $getProgressAction->execute($student, $course);

        $this->assertEquals(2, $progress['completed_lessons']);
        $this->assertEquals(100, $progress['overall_percentage']);

        // 7. Verify course completion
        $this->assertDatabaseHas('course_completions', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }
}
