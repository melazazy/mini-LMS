<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EnrollmentButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_enroll_button_for_non_enrolled_user()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create([
            'is_published' => true,
            'price' => null, // Free course
        ]);

        $this->actingAs($user);

        Livewire::test('enrollment-button', ['course' => $course])
            ->assertSee('Enroll for Free')
            ->assertSet('isEnrolled', false);
    }

    public function test_shows_enrolled_status_for_enrolled_user()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);

        // Enroll user
        $user->enrolledCourses()->attach($course->id, ['status' => 'active']);

        $this->actingAs($user);

        Livewire::test('enrollment-button', ['course' => $course])
            ->assertSee('Enrolled')
            ->assertSet('isEnrolled', true);
    }

    public function test_can_enroll_in_free_course()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create([
            'is_published' => true,
            'price' => null,
        ]);

        $this->actingAs($user);

        Livewire::test('enrollment-button', ['course' => $course])
            ->call('enroll')
            ->assertSet('isEnrolled', true)
            ->assertDispatched('enrolled');

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }

    public function test_shows_enroll_button_for_paid_course()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create([
            'is_published' => true,
            'price' => 49.99,
            'currency' => 'USD',
        ]);

        $this->actingAs($user);

        Livewire::test('enrollment-button', ['course' => $course])
            ->assertSee('Enroll for')
            ->assertSet('isEnrolled', false);
    }

    public function test_guest_cannot_enroll()
    {
        $course = Course::factory()->create([
            'is_published' => true,
            'price' => null,
        ]);

        Livewire::test('enrollment-button', ['course' => $course])
            ->call('enroll');

        $this->assertDatabaseMissing('enrollments', [
            'course_id' => $course->id,
        ]);
    }

    public function test_shows_loading_state_during_enrollment()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create([
            'is_published' => true,
            'price' => null,
        ]);

        $this->actingAs($user);

        Livewire::test('enrollment-button', ['course' => $course])
            ->assertSet('isLoading', false)
            ->call('enroll')
            ->assertSet('isEnrolled', true);
    }

    public function test_cannot_enroll_twice_in_same_course()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create([
            'is_published' => true,
            'price' => null,
        ]);

        // First enrollment
        $user->enrolledCourses()->attach($course->id, ['status' => 'active']);

        $this->actingAs($user);

        Livewire::test('enrollment-button', ['course' => $course])
            ->assertSet('isEnrolled', true)
            ->assertSee('Enrolled');

        // Verify only one enrollment exists
        $this->assertEquals(1, $user->enrolledCourses()->count());
    }
}
