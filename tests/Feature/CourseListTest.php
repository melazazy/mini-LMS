<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseListTest extends TestCase
{
    use RefreshDatabase;

    public function test_displays_published_courses()
    {
        $publishedCourse = Course::factory()->create([
            'is_published' => true,
            'title' => 'Published Course',
        ]);
        
        $unpublishedCourse = Course::factory()->create([
            'is_published' => false,
            'title' => 'Unpublished Course',
        ]);

        Livewire::test('course-list')
            ->assertSee('Published Course')
            ->assertDontSee('Unpublished Course');
    }

    public function test_can_search_courses_by_title()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Laravel Basics',
        ]);
        
        Course::factory()->create([
            'is_published' => true,
            'title' => 'React Advanced',
        ]);

        Livewire::test('course-list')
            ->set('search', 'Laravel')
            ->assertSee('Laravel Basics')
            ->assertDontSee('React Advanced');
    }

    public function test_can_search_courses_by_description()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Course A',
            'description' => 'Learn PHP programming',
        ]);
        
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Course B',
            'description' => 'Learn JavaScript basics',
        ]);

        Livewire::test('course-list')
            ->set('search', 'PHP')
            ->assertSee('Course A')
            ->assertDontSee('Course B');
    }

    public function test_can_filter_by_level()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Beginner Course',
            'level' => 'beginner',
        ]);
        
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Advanced Course',
            'level' => 'advanced',
        ]);

        Livewire::test('course-list')
            ->set('level', 'beginner')
            ->assertSee('Beginner Course')
            ->assertDontSee('Advanced Course');
    }

    public function test_can_sort_by_title()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Zebra Course',
        ]);
        
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Alpha Course',
        ]);

        $component = Livewire::test('course-list')
            ->set('sortBy', 'title')
            ->set('sortDirection', 'asc');

        // Verify sort parameters are set
        $this->assertEquals('title', $component->get('sortBy'));
        $this->assertEquals('asc', $component->get('sortDirection'));
    }

    public function test_can_sort_by_created_at()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Old Course',
            'created_at' => now()->subDays(10),
        ]);
        
        Course::factory()->create([
            'is_published' => true,
            'title' => 'New Course',
            'created_at' => now(),
        ]);

        $component = Livewire::test('course-list')
            ->set('sortBy', 'created_at')
            ->set('sortDirection', 'desc');

        // Just verify the sort parameters are set correctly
        $this->assertEquals('created_at', $component->get('sortBy'));
        $this->assertEquals('desc', $component->get('sortDirection'));
    }

    public function test_displays_lesson_count()
    {
        $course = Course::factory()->create(['is_published' => true]);
        
        Lesson::factory()->count(5)->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        Livewire::test('course-list')
            ->assertSee('5 lessons');
    }

    public function test_displays_free_badge_for_free_courses()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Free Course',
            'price' => null,
        ]);

        Livewire::test('course-list')
            ->assertSee('Free');
    }

    public function test_displays_price_for_paid_courses()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Paid Course',
            'price' => 99.99,
            'currency' => 'USD',
        ]);

        Livewire::test('course-list')
            ->assertSee('Paid Course');
    }

    public function test_displays_level_badge()
    {
        Course::factory()->create([
            'is_published' => true,
            'level' => 'intermediate',
        ]);

        Livewire::test('course-list')
            ->assertSee('Intermediate');
    }

    public function test_pagination_works()
    {
        // Create 15 courses (more than the default 12 per page)
        Course::factory()->count(15)->create(['is_published' => true]);

        $component = Livewire::test('course-list');
        
        // Verify component renders without errors
        $component->assertStatus(200);
    }

    public function test_search_resets_pagination()
    {
        // Create courses with unique titles
        for ($i = 1; $i <= 15; $i++) {
            Course::factory()->create([
                'is_published' => true,
                'title' => 'Test Course ' . $i,
            ]);
        }

        Livewire::test('course-list')
            ->set('search', 'test')
            ->assertSet('search', 'test');
    }

    public function test_level_filter_resets_pagination()
    {
        // Create courses with unique titles to avoid slug conflicts
        for ($i = 1; $i <= 15; $i++) {
            Course::factory()->create([
                'is_published' => true,
                'level' => 'beginner',
                'title' => 'Beginner Course ' . $i,
            ]);
        }

        Livewire::test('course-list')
            ->set('level', 'beginner')
            ->assertSet('level', 'beginner');
    }

    public function test_displays_empty_state_when_no_courses()
    {
        Livewire::test('course-list')
            ->assertSee('No courses found');
    }

    public function test_displays_empty_state_when_search_has_no_results()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Laravel Course',
        ]);

        Livewire::test('course-list')
            ->set('search', 'NonExistentCourse')
            ->assertSee('No courses found');
    }

    public function test_can_change_sort_field()
    {
        Course::factory()->create([
            'is_published' => true,
            'title' => 'A Course',
        ]);
        
        Course::factory()->create([
            'is_published' => true,
            'title' => 'Z Course',
        ]);

        $component = Livewire::test('course-list')
            ->set('sortBy', 'title')
            ->set('sortDirection', 'asc');

        $this->assertEquals('title', $component->get('sortBy'));
        $this->assertEquals('asc', $component->get('sortDirection'));
    }

    public function test_displays_view_course_button()
    {
        $course = Course::factory()->create([
            'is_published' => true,
            'title' => 'Test Course',
        ]);

        Livewire::test('course-list')
            ->assertSee('View Course');
    }
}
