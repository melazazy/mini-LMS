<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $instructor = User::where('role', 'instructor')->first();
        $admin = User::where('role', 'admin')->first();

        // Free courses
        Course::create([
            'title' => 'Introduction to Web Development',
            'slug' => 'introduction-to-web-development',
            'description' => 'Learn the basics of HTML, CSS, and JavaScript for web development.',
            'level' => 'beginner',
            'price' => null,
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $instructor->id,
            'free_lesson_count' => 3,
            'thumbnail_url' => 'https://via.placeholder.com/400x300/3b82f6/ffffff?text=Web+Dev',
        ]);

        Course::create([
            'title' => 'Advanced JavaScript Concepts',
            'slug' => 'advanced-javascript-concepts',
            'description' => 'Deep dive into advanced JavaScript patterns, closures, and modern ES6+ features.',
            'level' => 'advanced',
            'price' => null,
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $admin->id,
            'free_lesson_count' => 2,
            'thumbnail_url' => 'https://via.placeholder.com/400x300/10b981/ffffff?text=JavaScript',
        ]);

        // Paid courses
        Course::create([
            'title' => 'Complete Laravel Mastery',
            'slug' => 'complete-laravel-mastery',
            'description' => 'Master Laravel framework from basics to advanced topics including testing and deployment.',
            'level' => 'intermediate',
            'price' => 99.99,
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $instructor->id,
            'free_lesson_count' => 2,
            'thumbnail_url' => 'https://via.placeholder.com/400x300/8b5cf6/ffffff?text=Laravel',
        ]);

        Course::create([
            'title' => 'React & Redux Complete Guide',
            'slug' => 'react-redux-complete-guide',
            'description' => 'Build modern web applications with React and Redux state management.',
            'level' => 'intermediate',
            'price' => 149.99,
            'is_published' => false,
            'created_by' => $instructor->id,
            'free_lesson_count' => 1,
            'thumbnail_url' => 'https://via.placeholder.com/400x300/06b6d4/ffffff?text=React',
        ]);

        Course::create([
            'title' => 'Database Design & Optimization',
            'slug' => 'database-design-optimization',
            'description' => 'Learn database design principles and performance optimization techniques.',
            'level' => 'advanced',
            'price' => 199.99,
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $admin->id,
            'free_lesson_count' => 1,
            'thumbnail_url' => 'https://via.placeholder.com/400x300/f59e0b/ffffff?text=Database',
        ]);
    }
}