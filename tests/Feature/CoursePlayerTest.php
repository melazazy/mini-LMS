<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CoursePlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_load_course_player_with_free_preview()
    {
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee($lesson->title)
            ->assertSee('Course Content');
    }

    public function test_can_switch_lessons()
    {
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

        Livewire::test('course-player', ['course' => $course])
            ->call('loadLesson', $lesson2->id)
            ->assertSee($lesson2->title);
    }

    public function test_enrolled_user_can_watch_non_free_lessons()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => false,
        ]);

        // Enroll user
        $user->enrolledCourses()->attach($course->id, [
            'status' => 'active',
        ]);

        $this->actingAs($user);

        Livewire::test('course-player', ['course' => $course])
            ->assertSet('canWatchLesson', true)
            ->assertSee($lesson->title);
    }

    public function test_non_enrolled_user_cannot_watch_locked_lessons()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => false,
        ]);

        $this->actingAs($user);

        Livewire::test('course-player', ['course' => $course])
            ->assertSet('canWatchLesson', false)
            ->assertSee('Lesson Locked');
    }

    public function test_can_navigate_to_next_lesson()
    {
        $course = Course::factory()->create(['is_published' => true]);
        $lesson1 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'order' => 1,
        ]);
        $lesson2 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'order' => 2,
        ]);

        Livewire::test('course-player', ['course' => $course, 'lesson' => $lesson1])
            ->call('nextLesson')
            ->assertSet('currentLesson.id', $lesson2->id);
    }

    public function test_can_navigate_to_previous_lesson()
    {
        $course = Course::factory()->create(['is_published' => true]);
        $lesson1 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'order' => 1,
        ]);
        $lesson2 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'order' => 2,
        ]);

        Livewire::test('course-player', ['course' => $course, 'lesson' => $lesson2])
            ->call('previousLesson')
            ->assertSet('currentLesson.id', $lesson1->id);
    }

    public function test_enrolled_user_can_update_progress()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'duration_seconds' => 600,
        ]);

        // Enroll user
        $user->enrolledCourses()->attach($course->id, [
            'status' => 'active',
        ]);

        $this->actingAs($user);

        Livewire::test('course-player', ['course' => $course, 'lesson' => $lesson])
            ->call('updateProgress', 50, 300)
            ->assertDispatched('progressUpdated');

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 50,
            'last_position_seconds' => 300,
        ]);
    }

    public function test_guest_cannot_update_progress()
    {
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
        ]);

        Livewire::test('course-player', ['course' => $course, 'lesson' => $lesson])
            ->call('updateProgress', 50, 300);

        $this->assertDatabaseMissing('lesson_progress', [
            'lesson_id' => $lesson->id,
        ]);
    }
}
