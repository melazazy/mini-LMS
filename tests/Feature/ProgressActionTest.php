<?php

namespace Tests\Feature;

use App\Actions\Progress\UpdateLessonProgressAction;
use App\Actions\Progress\GetUserProgressAction;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_lesson_progress()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        // Enroll user in course
        Enrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $action = app(UpdateLessonProgressAction::class);
        $progress = $action->execute($user, $lesson, 75, 120);

        $this->assertInstanceOf(LessonProgress::class, $progress);
        $this->assertEquals(75, $progress->watched_percentage);
        $this->assertEquals(120, $progress->last_position_seconds);
        
        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 75,
            'last_position_seconds' => 120,
        ]);
    }

    public function test_can_watch_free_preview_lesson_without_enrollment()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
        ]);

        $action = app(UpdateLessonProgressAction::class);
        $progress = $action->execute($user, $lesson, 50, 60);

        $this->assertInstanceOf(LessonProgress::class, $progress);
        $this->assertEquals(50, $progress->watched_percentage);
    }

    public function test_cannot_watch_lesson_without_enrollment()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => false,
        ]);

        $action = app(UpdateLessonProgressAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not authorized to watch this lesson.');
        
        $action->execute($user, $lesson, 50, 60);
    }

    public function test_lesson_completion_triggers_course_completion()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        
        // Create lessons
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

        // Enroll user in course
        Enrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $action = app(UpdateLessonProgressAction::class);

        // Complete first lesson
        $action->execute($user, $lesson1, 90, 300);

        // Complete second lesson (should trigger course completion)
        $action->execute($user, $lesson2, 90, 300);

        $this->assertDatabaseHas('course_completions', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_can_get_user_progress()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        
        // Create lessons
        $lesson1 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'order' => 1,
            'duration_seconds' => 300,
        ]);
        
        $lesson2 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'order' => 2,
            'duration_seconds' => 600,
        ]);

        // Enroll user in course
        Enrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Create progress for first lesson
        LessonProgress::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson1->id,
            'watched_percentage' => 90,
            'last_position_seconds' => 270,
        ]);

        $action = app(GetUserProgressAction::class);
        $progress = $action->execute($user, $course);

        $this->assertIsArray($progress);
        $this->assertEquals($course->id, $progress['course_id']);
        $this->assertEquals($course->title, $progress['course_title']);
        $this->assertEquals(2, $progress['total_lessons']);
        $this->assertEquals(1, $progress['completed_lessons']);
        $this->assertEquals(0, $progress['in_progress_lessons']);
        $this->assertEquals(1, $progress['not_started_lessons']);
        $this->assertEquals(50.0, $progress['overall_percentage']);
        $this->assertCount(2, $progress['lessons']);
    }

    public function test_cannot_get_progress_for_unenrolled_course()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);

        $action = app(GetUserProgressAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not enrolled in this course.');
        
        $action->execute($user, $course);
    }

    public function test_invalid_percentage_throws_exception()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
        ]);

        $action = app(UpdateLessonProgressAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid watched percentage.');
        
        $action->execute($user, $lesson, 150, 60);
    }
}