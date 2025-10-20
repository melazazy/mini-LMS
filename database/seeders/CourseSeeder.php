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
            'title' => 'Web Development Fundamentals',
            'slug' => 'web-development-fundamentals',
            'description' => 'Master the essential building blocks of web development. Learn HTML5 for structure, CSS3 for styling, and JavaScript for interactivity. Perfect for absolute beginners who want to start their journey in web development. Build real projects including a portfolio website, landing pages, and interactive web applications.',
            'level' => 'beginner',
            'price' => null,
            'is_published' => true,
            'published_at' => now()->subDays(30),
            'created_by' => $instructor->id,
            'free_lesson_count' => 3,
            'thumbnail_url' => null,
        ]);

        Course::create([
            'title' => 'Modern JavaScript ES6+',
            'slug' => 'modern-javascript-es6-plus',
            'description' => 'Deep dive into modern JavaScript with ES6+ features. Master arrow functions, destructuring, spread operators, promises, async/await, modules, and more. Learn functional programming concepts, closures, and advanced patterns used in production applications. Includes real-world projects and best practices.',
            'level' => 'intermediate',
            'price' => null,
            'is_published' => true,
            'published_at' => now()->subDays(25),
            'created_by' => $admin->id,
            'free_lesson_count' => 2,
            'thumbnail_url' => null,
        ]);

        // Paid courses
        Course::create([
            'title' => 'Laravel 11 - Complete Web Development',
            'slug' => 'laravel-11-complete-web-development',
            'description' => 'Become a Laravel expert! Build professional web applications from scratch using Laravel 11. Master routing, controllers, Eloquent ORM, authentication, authorization, testing, API development, and deployment. Learn best practices, design patterns, and real-world development workflows. Includes 3 complete projects: Blog platform, E-commerce site, and REST API.',
            'level' => 'intermediate',
            'price' => 99.99,
            'is_published' => true,
            'published_at' => now()->subDays(20),
            'created_by' => $instructor->id,
            'free_lesson_count' => 2,
            'thumbnail_url' => null,
        ]);

        Course::create([
            'title' => 'React 18 & Redux Toolkit Masterclass',
            'slug' => 'react-18-redux-toolkit-masterclass',
            'description' => 'Master modern React development with React 18 and Redux Toolkit. Learn hooks, context API, performance optimization, server components, and state management. Build scalable applications with TypeScript, React Router, and best practices. Create 4 production-ready projects including a social media app, task manager, e-commerce store, and real-time chat application.',
            'level' => 'intermediate',
            'price' => 129.99,
            'is_published' => false,
            'created_by' => $instructor->id,
            'free_lesson_count' => 1,
            'thumbnail_url' => null,
        ]);

        Course::create([
            'title' => 'Advanced Database Design & SQL Optimization',
            'slug' => 'advanced-database-design-sql-optimization',
            'description' => 'Master database design principles and SQL optimization techniques for high-performance applications. Learn normalization, indexing strategies, query optimization, transaction management, and database security. Cover MySQL, PostgreSQL, and MongoDB. Includes performance tuning, scaling strategies, and real-world case studies from enterprise applications.',
            'level' => 'advanced',
            'price' => 149.99,
            'is_published' => true,
            'published_at' => now()->subDays(15),
            'created_by' => $admin->id,
            'free_lesson_count' => 1,
            'thumbnail_url' => null,
        ]);

        // Additional paid course
        Course::create([
            'title' => 'Full-Stack JavaScript with Node.js & Express',
            'slug' => 'full-stack-javascript-nodejs-express',
            'description' => 'Become a full-stack JavaScript developer! Build complete web applications using Node.js, Express, MongoDB, and React. Learn RESTful API design, authentication with JWT, real-time features with Socket.io, payment integration, file uploads, and deployment to production. Build 3 full-stack projects: Social network, Job board, and SaaS application.',
            'level' => 'intermediate',
            'price' => 119.99,
            'is_published' => true,
            'published_at' => now()->subDays(10),
            'created_by' => $instructor->id,
            'free_lesson_count' => 2,
            'thumbnail_url' => null,
        ]);
    }
}