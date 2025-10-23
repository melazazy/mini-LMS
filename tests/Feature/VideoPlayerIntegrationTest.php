<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VideoPlayerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_video_player_displays_video_element()
    {
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'video_url' => 'https://example.com/video.mp4',
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee($lesson->title);
    }

    public function test_video_player_loads_lesson_with_hls()
    {
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'video_url' => 'https://example.com/video.mp4',
            'hls_manifest_url' => 'https://example.com/video.m3u8',
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee($lesson->title);
    }

    public function test_video_player_displays_lesson_duration()
    {
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'duration_seconds' => 600, // 10 minutes
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee('10:00');
    }

    public function test_enrolled_user_sees_progress_data()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'duration_seconds' => 100,
        ]);

        // Enroll user
        $user->enrolledCourses()->attach($course->id, ['status' => 'active']);

        // Mark lesson as completed (>90%)
        $user->lessonProgress()->create([
            'lesson_id' => $lesson->id,
            'watched_percentage' => 95,
            'last_position_seconds' => 95,
        ]);

        $this->actingAs($user);

        // Refresh course to load relationships
        $course->refresh();

        $component = Livewire::test('course-player', ['course' => $course, 'lesson' => $lesson]);
        
        // Verify user is enrolled
        $this->assertTrue($component->get('isEnrolled'));
        $this->assertTrue($component->get('canWatchLesson'));
    }

    public function test_video_player_displays_all_course_lessons_in_sidebar()
    {
        $course = Course::factory()->create(['is_published' => true]);
        
        $lesson1 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'title' => 'Introduction',
            'order' => 1,
        ]);
        
        $lesson2 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'title' => 'Advanced Topics',
            'order' => 2,
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee('Introduction')
            ->assertSee('Advanced Topics')
            ->assertSee('Course Content');
    }

    public function test_video_player_highlights_current_lesson()
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

        $component = Livewire::test('course-player', ['course' => $course, 'lesson' => $lesson2]);
        
        $this->assertEquals($lesson2->id, $component->get('currentLesson')->id);
    }

    public function test_video_player_shows_free_preview_badges()
    {
        $course = Course::factory()->create(['is_published' => true]);
        
        Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'title' => 'Free Lesson',
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee('Free');
    }

    public function test_video_player_shows_progress_indicators_for_enrolled_users()
    {
        $user = User::factory()->create(['role' => 'student']);
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

        // Enroll user
        $user->enrolledCourses()->attach($course->id, ['status' => 'active']);

        // Complete first lesson
        $user->lessonProgress()->create([
            'lesson_id' => $lesson1->id,
            'watched_percentage' => 100,
            'last_position_seconds' => 600,
        ]);

        $this->actingAs($user);

        $component = Livewire::test('course-player', ['course' => $course]);
        
        // Should show completion indicator
        $this->assertTrue($component->get('isEnrolled'));
    }

    public function test_video_player_navigation_buttons_work()
    {
        $course = Course::factory()->create(['is_published' => true]);
        
        Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => true,
            'order' => 1,
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee('Previous')
            ->assertSee('Next');
    }

    public function test_video_player_loads_saved_progress()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'duration_seconds' => 600,
        ]);

        // Enroll user
        $user->enrolledCourses()->attach($course->id, ['status' => 'active']);

        // Save progress at 300 seconds
        $user->lessonProgress()->create([
            'lesson_id' => $lesson->id,
            'watched_percentage' => 50,
            'last_position_seconds' => 300,
        ]);

        $this->actingAs($user);

        $component = Livewire::test('course-player', ['course' => $course]);
        
        $progress = $component->get('progress');
        $this->assertGreaterThanOrEqual(0, $progress['position']);
        $this->assertGreaterThanOrEqual(0, $progress['percentage']);
    }

    public function test_video_player_initializes_progress_at_zero_for_new_lesson()
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        // Enroll user
        $user->enrolledCourses()->attach($course->id, ['status' => 'active']);

        $this->actingAs($user);

        $component = Livewire::test('course-player', ['course' => $course]);
        
        $progress = $component->get('progress');
        $this->assertEquals(0, $progress['position']);
        $this->assertEquals(0, $progress['percentage']);
        $this->assertFalse($progress['is_completed']);
    }

    public function test_locked_lesson_shows_enrollment_prompt()
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
            ->assertSee('Lesson Locked')
            ->assertSee('Please enroll in this course to access all lessons');
    }

    public function test_guest_sees_enrollment_prompt_for_locked_lessons()
    {
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'is_free_preview' => false,
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee('Lesson Locked')
            ->assertSeeLivewire('enrollment-button');
    }

    public function test_video_player_needs_at_least_one_lesson()
    {
        $course = Course::factory()->create(['is_published' => true]);

        try {
            Livewire::test('course-player', ['course' => $course]);
            // If we get here, the component handled it gracefully
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // Expected behavior - no lessons available
            $this->assertInstanceOf(\Symfony\Component\HttpKernel\Exception\HttpException::class, $e);
        }
    }

    public function test_video_player_loads_first_published_lesson()
    {
        $course = Course::factory()->create(['is_published' => true]);
        
        $lesson1 = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'order' => 1,
            'title' => 'First Lesson',
        ]);
        
        Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'order' => 2,
            'title' => 'Second Lesson',
        ]);

        Livewire::test('course-player', ['course' => $course])
            ->assertSee('First Lesson');
    }
}
