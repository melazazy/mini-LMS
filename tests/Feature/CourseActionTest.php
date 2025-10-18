<?php

namespace Tests\Feature;

use App\Actions\Course\CreateCourseAction;
use App\Actions\Course\PublishCourseAction;
use App\Actions\Course\CreateLessonAction;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_create_course()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        
        $courseData = [
            'title' => 'Test Course',
            'description' => 'A test course description',
            'level' => 'beginner',
            'price' => 99.99,
            'currency' => 'USD',
            'thumbnail_url' => 'https://example.com/thumbnail.jpg',
            'free_lesson_count' => 2,
        ];

        $action = app(CreateCourseAction::class);
        $course = $action->execute($instructor, $courseData);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('Test Course', $course->title);
        $this->assertEquals($instructor->id, $course->created_by);
        $this->assertFalse($course->is_published);
        
        $this->assertDatabaseHas('courses', [
            'title' => 'Test Course',
            'created_by' => $instructor->id,
            'is_published' => false,
        ]);

        // Check moderation review was created
        $this->assertDatabaseHas('moderation_reviews', [
            'subject_type' => 'course',
            'subject_id' => $course->id,
            'state' => 'draft',
            'submitted_by' => $instructor->id,
        ]);
    }

    public function test_admin_can_create_course()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $courseData = [
            'title' => 'Admin Course',
            'description' => 'An admin created course',
            'level' => 'advanced',
            'price' => null, // Free course
        ];

        $action = app(CreateCourseAction::class);
        $course = $action->execute($admin, $courseData);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('Admin Course', $course->title);
        $this->assertEquals($admin->id, $course->created_by);
    }

    public function test_student_cannot_create_course()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $courseData = [
            'title' => 'Student Course',
            'description' => 'A course created by student',
            'level' => 'beginner',
        ];

        $action = app(CreateCourseAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not authorized to create courses.');
        
        $action->execute($student, $courseData);
    }

    public function test_course_creator_can_publish_course()
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
        
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'is_published' => true,
        ]);
    }

    public function test_admin_can_publish_any_course()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'created_by' => $instructor->id,
            'is_published' => false,
        ]);

        $action = app(PublishCourseAction::class);
        $publishedCourse = $action->execute($admin, $course);

        $this->assertTrue($publishedCourse->is_published);
    }

    public function test_instructor_cannot_publish_other_instructor_course()
    {
        $instructor1 = User::factory()->create(['role' => 'instructor']);
        $instructor2 = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'created_by' => $instructor1->id,
            'is_published' => false,
        ]);

        $action = app(PublishCourseAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not authorized to publish this course.');
        
        $action->execute($instructor2, $course);
    }

    public function test_instructor_can_create_lesson_for_own_course()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);
        
        $lessonData = [
            'title' => 'Test Lesson',
            'video_url' => 'https://example.com/video.mp4',
            'duration_seconds' => 300,
            'is_free_preview' => true,
            'resources' => ['pdf1.pdf', 'pdf2.pdf'],
        ];

        $action = app(CreateLessonAction::class);
        $lesson = $action->execute($instructor, $course, $lessonData);

        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertEquals('Test Lesson', $lesson->title);
        $this->assertEquals($course->id, $lesson->course_id);
        $this->assertEquals(1, $lesson->order);
        $this->assertFalse($lesson->is_published);
        
        $this->assertDatabaseHas('lessons', [
            'title' => 'Test Lesson',
            'course_id' => $course->id,
            'order' => 1,
        ]);

        // Check moderation review was created
        $this->assertDatabaseHas('moderation_reviews', [
            'subject_type' => 'lesson',
            'subject_id' => $lesson->id,
            'state' => 'draft',
            'submitted_by' => $instructor->id,
        ]);
    }

    public function test_admin_can_create_lesson_for_any_course()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);
        
        $lessonData = [
            'title' => 'Admin Lesson',
            'video_url' => 'https://example.com/admin-video.mp4',
            'duration_seconds' => 600,
        ];

        $action = app(CreateLessonAction::class);
        $lesson = $action->execute($admin, $course, $lessonData);

        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertEquals('Admin Lesson', $lesson->title);
    }

    public function test_instructor_cannot_create_lesson_for_other_course()
    {
        $instructor1 = User::factory()->create(['role' => 'instructor']);
        $instructor2 = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor1->id]);
        
        $lessonData = [
            'title' => 'Unauthorized Lesson',
            'video_url' => 'https://example.com/video.mp4',
        ];

        $action = app(CreateLessonAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not authorized to create lessons for this course.');
        
        $action->execute($instructor2, $course, $lessonData);
    }

    public function test_lesson_order_is_automatically_assigned()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['created_by' => $instructor->id]);
        
        // Create first lesson
        Lesson::factory()->create([
            'course_id' => $course->id,
            'order' => 1,
        ]);
        
        // Create second lesson
        Lesson::factory()->create([
            'course_id' => $course->id,
            'order' => 2,
        ]);
        
        $lessonData = [
            'title' => 'Third Lesson',
            'video_url' => 'https://example.com/video.mp4',
        ];

        $action = app(CreateLessonAction::class);
        $lesson = $action->execute($instructor, $course, $lessonData);

        $this->assertEquals(3, $lesson->order);
    }
}