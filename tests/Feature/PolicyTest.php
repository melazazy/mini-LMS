<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_policy_view_any()
    {
        $user = User::factory()->create();
        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Course::class));
    }

    public function test_course_policy_view_published_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $course));
    }

    public function test_course_policy_view_unpublished_course_as_creator()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'is_published' => false,
            'created_by' => $instructor->id,
        ]);

        $this->assertTrue(Gate::forUser($instructor)->allows('view', $course));
    }

    public function test_course_policy_view_unpublished_course_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'is_published' => false,
            'created_by' => $instructor->id,
        ]);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $course));
    }

    public function test_course_policy_cannot_view_unpublished_course_as_student()
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'is_published' => false,
            'created_by' => $instructor->id,
        ]);

        $this->assertFalse(Gate::forUser($student)->allows('view', $course));
    }

    public function test_course_policy_create()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->assertTrue(Gate::forUser($instructor)->allows('create', Course::class));
        $this->assertTrue(Gate::forUser($admin)->allows('create', Course::class));
        $this->assertFalse(Gate::forUser($student)->allows('create', Course::class));
    }

    public function test_course_policy_enroll()
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['is_published' => true]);

        $this->assertTrue(Gate::forUser($student)->allows('enroll', $course));
        $this->assertFalse(Gate::forUser($instructor)->allows('enroll', $course));
    }

    public function test_lesson_policy_view_free_preview()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_free_preview' => true,
            'is_published' => true,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $lesson));
    }

    public function test_lesson_policy_watch_free_preview()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_free_preview' => true,
            'is_published' => true,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('watch', $lesson));
    }

    public function test_enrollment_policy_view_any()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Enrollment::class));
        $this->assertTrue(Gate::forUser($instructor)->allows('viewAny', Enrollment::class));
        $this->assertFalse(Gate::forUser($student)->allows('viewAny', Enrollment::class));
    }

    public function test_enrollment_policy_create()
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $this->assertTrue(Gate::forUser($student)->allows('create', Enrollment::class));
        $this->assertFalse(Gate::forUser($instructor)->allows('create', Enrollment::class));
    }

    public function test_gates_work_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        // Test manage-content gate
        $this->assertTrue(Gate::forUser($admin)->allows('manage-content'));
        $this->assertTrue(Gate::forUser($instructor)->allows('manage-content'));
        $this->assertFalse(Gate::forUser($student)->allows('manage-content'));

        // Test manage-users gate
        $this->assertTrue(Gate::forUser($admin)->allows('manage-users'));
        $this->assertFalse(Gate::forUser($instructor)->allows('manage-users'));
        $this->assertFalse(Gate::forUser($student)->allows('manage-users'));

        // Test moderate-content gate
        $this->assertTrue(Gate::forUser($admin)->allows('moderate-content'));
        $this->assertFalse(Gate::forUser($instructor)->allows('moderate-content'));
        $this->assertFalse(Gate::forUser($student)->allows('moderate-content'));
    }
}