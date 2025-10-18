<?php

namespace Tests\Feature;

use App\Actions\Enrollment\EnrollInCourseAction;
use App\Actions\Enrollment\EnrollInFreeCourseAction;
use App\Actions\Enrollment\CancelEnrollmentAction;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_enroll_in_free_course()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['price' => null, 'is_published' => true]);

        $action = app(EnrollInFreeCourseAction::class);
        $enrollment = $action->execute($user, $course);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'paid_amount' => null,
        ]);

        $this->assertInstanceOf(Enrollment::class, $enrollment);
    }

    public function test_can_enroll_in_paid_course()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['price' => 99.99, 'is_published' => true]);

        $action = app(EnrollInCourseAction::class);
        $enrollment = $action->execute($user, $course, [
            'payment_id' => 'stripe_payment_123',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'paid_amount' => 99.99,
            'payment_id' => 'stripe_payment_123',
        ]);

        $this->assertInstanceOf(Enrollment::class, $enrollment);
    }

    public function test_cannot_enroll_in_unpublished_course()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['price' => null, 'is_published' => false]);

        $action = app(EnrollInFreeCourseAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Course is not available for enrollment.');
        
        $action->execute($user, $course);
    }

    public function test_cannot_enroll_twice_in_same_course()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['price' => null, 'is_published' => true]);

        $action = app(EnrollInFreeCourseAction::class);
        
        // First enrollment should succeed
        $action->execute($user, $course);

        // Second enrollment should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is already enrolled in this course.');
        
        $action->execute($user, $course);
    }

    public function test_cannot_enroll_in_paid_course_with_free_action()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['price' => 99.99, 'is_published' => true]);

        $action = app(EnrollInFreeCourseAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Course is not free. Use paid enrollment action.');
        
        $action->execute($user, $course);
    }

    public function test_can_cancel_enrollment()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $action = app(CancelEnrollmentAction::class);
        $result = $action->execute($user, $enrollment);

        $this->assertTrue($result);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'canceled',
        ]);
    }

    public function test_admin_can_cancel_any_enrollment()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $action = app(CancelEnrollmentAction::class);
        $result = $action->execute($admin, $enrollment);

        $this->assertTrue($result);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'canceled',
        ]);
    }

    public function test_cannot_cancel_inactive_enrollment()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'canceled',
        ]);

        $action = app(CancelEnrollmentAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Enrollment is not active and cannot be cancelled.');
        
        $action->execute($user, $enrollment);
    }
}