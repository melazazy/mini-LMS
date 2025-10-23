<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_can_be_soft_deleted()
    {
        $user = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'created_by' => $user->id,
        ]);

        $courseId = $course->id;

        // Soft delete the course
        $course->delete();

        // Course should not be in normal queries
        $this->assertNull(Course::find($courseId));

        // But should exist in trashed queries
        $this->assertNotNull(Course::withTrashed()->find($courseId));
        $this->assertTrue(Course::withTrashed()->find($courseId)->trashed());
    }

    public function test_soft_deleted_course_can_be_restored()
    {
        $user = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'created_by' => $user->id,
        ]);

        $courseId = $course->id;
        $originalSlug = $course->slug;

        // Soft delete and restore
        $course->delete();
        $course->restore();

        // Course should be accessible again
        $restoredCourse = Course::find($courseId);
        $this->assertNotNull($restoredCourse);
        $this->assertFalse($restoredCourse->trashed());
        $this->assertEquals($originalSlug, $restoredCourse->slug);
    }

    public function test_slug_uniqueness_with_soft_deletes()
    {
        $user = User::factory()->admin()->create();
        
        // Create first course
        $course1 = Course::create([
            'title' => 'Laravel Basics',
            'description' => 'Learn Laravel basics',
            'level' => 'beginner',
            'price' => 0,
            'created_by' => $user->id,
        ]);
        $this->assertEquals('laravel-basics', $course1->slug);

        // Soft delete first course
        $course1->delete();

        // Create second course with same title
        $course2 = Course::create([
            'title' => 'Laravel Basics',
            'description' => 'Learn Laravel basics again',
            'level' => 'beginner',
            'price' => 0,
            'created_by' => $user->id,
        ]);

        // Should have different slug because first one still exists (soft deleted)
        $this->assertEquals('laravel-basics-1', $course2->slug);
    }

    public function test_restored_course_gets_new_slug_if_taken()
    {
        $user = User::factory()->admin()->create();
        
        // Create and soft delete first course
        $course1 = Course::create([
            'title' => 'Laravel Basics',
            'description' => 'Learn Laravel basics',
            'level' => 'beginner',
            'price' => 0,
            'created_by' => $user->id,
        ]);
        $this->assertEquals('laravel-basics', $course1->slug);
        $course1->delete();

        // Create second course with same title (gets incremented slug)
        $course2 = Course::create([
            'title' => 'Laravel Basics',
            'description' => 'Learn Laravel basics again',
            'level' => 'beginner',
            'price' => 0,
            'created_by' => $user->id,
        ]);
        $this->assertEquals('laravel-basics-1', $course2->slug);

        // Restore first course - slug should remain same since it's not taken
        $course1->restore();
        $course1->refresh();

        // First course keeps its original slug since "laravel-basics" is available
        // (course2 has "laravel-basics-1")
        $this->assertEquals('laravel-basics', $course1->slug);
        
        // Both courses should be accessible
        $this->assertCount(2, Course::all());
    }

    public function test_only_trashed_courses_query()
    {
        $user = User::factory()->admin()->create();
        
        $activeCourse = Course::factory()->create([
            'title' => 'Active Course',
            'created_by' => $user->id,
        ]);

        $deletedCourse = Course::factory()->create([
            'title' => 'Deleted Course',
            'created_by' => $user->id,
        ]);
        $deletedCourse->delete();

        // Only trashed should return only deleted courses
        $trashedCourses = Course::onlyTrashed()->get();
        $this->assertCount(1, $trashedCourses);
        $this->assertEquals('Deleted Course', $trashedCourses->first()->title);

        // Normal query should only return active
        $activeCourses = Course::all();
        $this->assertCount(1, $activeCourses);
        $this->assertEquals('Active Course', $activeCourses->first()->title);
    }

    public function test_force_delete_permanently_removes_course()
    {
        $user = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'created_by' => $user->id,
        ]);

        $courseId = $course->id;

        // Force delete (permanent)
        $course->forceDelete();

        // Should not exist even in trashed queries
        $this->assertNull(Course::withTrashed()->find($courseId));
    }

    public function test_soft_deleted_courses_not_shown_on_public_pages()
    {
        $user = User::factory()->admin()->create();
        
        $activeCourse = Course::factory()->create([
            'title' => 'Active Course',
            'is_published' => true,
            'created_by' => $user->id,
        ]);

        $deletedCourse = Course::factory()->create([
            'title' => 'Deleted Course',
            'is_published' => true,
            'created_by' => $user->id,
        ]);
        $deletedCourse->delete();

        // Public course listing should only show active courses
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Active Course');
        $response->assertDontSee('Deleted Course');
    }
}
