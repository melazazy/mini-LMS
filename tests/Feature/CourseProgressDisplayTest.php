<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseProgressDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_updates_in_real_time()
    {
        // Create a student user
        $student = User::factory()->student()->create();

        // Create a course with 4 lessons
        $course = Course::factory()->published()->create();
        $lessons = Lesson::factory()->count(4)->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        // Enroll the student
        Enrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Act as the student
        $this->actingAs($student);

        // Load the course player
        $component = Livewire::test(\App\Livewire\CoursePlayer::class, [
            'course' => $course,
            'lesson' => $lessons->first(),
        ]);

        // Initially, no lessons are completed
        $this->assertEquals(0, $component->get('completedLessons'));
        $this->assertEquals(0, $component->get('progressPercentage'));

        // Mark first lesson as 100% complete
        $component->call('updateProgress', 100, 100);

        // Progress should now show 1/4 completed (25%)
        $this->assertEquals(1, $component->get('completedLessons'));
        $this->assertEquals(25, $component->get('progressPercentage'));

        // Mark second lesson as 95% complete (above 90% threshold)
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lessons[1]->id,
            'watched_percentage' => 95,
            'last_position_seconds' => 100,
            'last_watched_at' => now(),
        ]);

        // Reload the component to reflect the change
        $component = Livewire::test(\App\Livewire\CoursePlayer::class, [
            'course' => $course,
            'lesson' => $lessons->first(),
        ]);

        // Progress should now show 2/4 completed (50%)
        $this->assertEquals(2, $component->get('completedLessons'));
        $this->assertEquals(50, $component->get('progressPercentage'));

        // Mark third lesson as 50% complete (below 90% threshold)
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lessons[2]->id,
            'watched_percentage' => 50,
            'last_position_seconds' => 50,
            'last_watched_at' => now(),
        ]);

        // Reload the component
        $component = Livewire::test(\App\Livewire\CoursePlayer::class, [
            'course' => $course,
            'lesson' => $lessons->first(),
        ]);

        // Progress should still show 2/4 completed (50%) because 50% < 90% threshold
        $this->assertEquals(2, $component->get('completedLessons'));
        $this->assertEquals(50, $component->get('progressPercentage'));

        // Mark all lessons as complete
        foreach ($lessons as $lesson) {
            LessonProgress::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'watched_percentage' => 100,
                    'last_position_seconds' => 100,
                    'last_watched_at' => now(),
                ]
            );
        }

        // Reload the component
        $component = Livewire::test(\App\Livewire\CoursePlayer::class, [
            'course' => $course,
            'lesson' => $lessons->first(),
        ]);

        // Progress should now show 4/4 completed (100%)
        $this->assertEquals(4, $component->get('completedLessons'));
        $this->assertEquals(100, $component->get('progressPercentage'));
    }

    public function test_progress_respects_completion_threshold()
    {
        // Set completion threshold to 90%
        config(['lms.lesson_completion_threshold' => 90]);

        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        Enrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->actingAs($student);

        // Test with 89% - should not be completed
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 89,
            'last_position_seconds' => 89,
            'last_watched_at' => now(),
        ]);

        $component = Livewire::test(\App\Livewire\CoursePlayer::class, [
            'course' => $course,
            'lesson' => $lesson,
        ]);

        $this->assertEquals(0, $component->get('completedLessons'));

        // Test with 90% - should be completed
        LessonProgress::updateOrCreate(
            [
                'user_id' => $student->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'watched_percentage' => 90,
                'last_position_seconds' => 90,
                'last_watched_at' => now(),
            ]
        );

        $component = Livewire::test(\App\Livewire\CoursePlayer::class, [
            'course' => $course,
            'lesson' => $lesson,
        ]);

        $this->assertEquals(1, $component->get('completedLessons'));
    }
}
