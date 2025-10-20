<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run()
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            $this->createLessonsForCourse($course);
        }
    }

    private function createLessonsForCourse(Course $course)
    {
        $lessons = $this->getLessonsData($course->title);

        foreach ($lessons as $index => $lessonData) {
            Lesson::create([
                'course_id' => $course->id,
                'title' => $lessonData['title'],
                'slug' => \Illuminate\Support\Str::slug($lessonData['title']),
                'description' => $lessonData['description'] ?? null,
                'video_url' => $lessonData['video_url'],
                'duration_seconds' => $lessonData['duration_seconds'],
                'is_published' => $lessonData['is_published'],
                'order' => $index + 1,
                'is_free_preview' => $index < $course->free_lesson_count,
                'resources' => $lessonData['resources'],
            ]);
        }
    }

    private function getLessonsData($courseTitle)
    {
        // Using free, open-source test videos from Google Cloud Storage
        $baseLessons = [
            [
                'title' => 'Course Introduction & What You\'ll Learn',
                'description' => 'Welcome! Get an overview of the entire course curriculum, understand the prerequisites, and learn how to maximize your learning experience. We\'ll discuss the projects you\'ll build and the skills you\'ll master by the end of this course.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'duration_seconds' => 420,
                'is_published' => true,
                'resources' => ['Course Syllabus PDF', 'Resource Links', 'Community Discord'],
            ],
            [
                'title' => 'Development Environment Setup',
                'description' => 'Set up your complete development environment from scratch. Install and configure all necessary tools including code editor, version control, package managers, and browser dev tools. Includes troubleshooting common installation issues across Windows, Mac, and Linux.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'duration_seconds' => 540,
                'is_published' => true,
                'resources' => ['Setup Checklist', 'VS Code Extensions', 'Terminal Commands Cheat Sheet'],
            ],
            [
                'title' => 'Core Concepts & Fundamentals',
                'description' => 'Master the essential concepts that everything else builds upon. Understand the theory, see practical examples, and learn why these fundamentals matter. This lesson covers syntax, data structures, and programming paradigms with clear explanations and visual diagrams.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'duration_seconds' => 720,
                'is_published' => true,
                'resources' => ['Concept Map PDF', 'Interactive Examples', 'Quick Reference Guide'],
            ],
            [
                'title' => 'Building Your First Project',
                'description' => 'Apply what you\'ve learned by building a complete project from start to finish. Follow along with step-by-step instructions, understand each decision, and see how all the pieces fit together. Includes debugging tips and common mistakes to avoid.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'duration_seconds' => 1080,
                'is_published' => true,
                'resources' => ['Starter Code', 'Final Solution', 'Project Assets'],
            ],
            [
                'title' => 'Advanced Techniques & Best Practices',
                'description' => 'Level up your skills with advanced patterns and professional best practices. Learn optimization techniques, code organization strategies, and industry-standard approaches used in production applications. Includes performance tips and scalability considerations.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'duration_seconds' => 900,
                'is_published' => true,
                'resources' => ['Best Practices Checklist', 'Code Style Guide', 'Performance Benchmarks'],
            ],
            [
                'title' => 'Capstone Project & Deployment',
                'description' => 'Build a comprehensive portfolio project that showcases all your new skills. Learn deployment strategies, hosting options, CI/CD pipelines, and how to maintain production applications. Get your project live and share it with the world!',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
                'duration_seconds' => 1440,
                'is_published' => true,
                'resources' => ['Project Requirements', 'Deployment Guide', 'Complete Source Code'],
            ],
        ];

        // Add course-specific lessons
        if (str_contains($courseTitle, 'Laravel')) {
            $baseLessons[] = [
                'title' => 'Laravel Routing & Controllers Deep Dive',
                'description' => 'Master Laravel\'s routing system and controller architecture. Learn route parameters, route model binding, resource controllers, and API routes. Understand middleware, route groups, and how to organize routes for large applications.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
                'duration_seconds' => 960,
                'is_published' => true,
                'resources' => ['Routing Cheat Sheet', 'Controller Templates', 'Middleware Examples'],
            ];
            $baseLessons[] = [
                'title' => 'Eloquent ORM & Database Relationships',
                'description' => 'Master Eloquent ORM for elegant database interactions. Learn migrations, model relationships (one-to-one, one-to-many, many-to-many), eager loading, query scopes, and advanced Eloquent features. Build complex database queries with ease.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                'duration_seconds' => 1080,
                'is_published' => true,
                'resources' => ['Database Schema Diagram', 'Model Examples', 'Query Builder Guide'],
            ];
            $baseLessons[] = [
                'title' => 'Authentication & Authorization System',
                'description' => 'Implement secure authentication and authorization in Laravel. Learn Laravel Breeze, Sanctum for API authentication, role-based access control, policies, gates, and middleware. Build a complete multi-role authentication system.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'duration_seconds' => 840,
                'is_published' => true,
                'resources' => ['Auth Flow Diagram', 'Policy Examples', 'Security Best Practices'],
            ];
        }

        if (str_contains($courseTitle, 'JavaScript') || str_contains($courseTitle, 'ES6')) {
            $baseLessons[] = [
                'title' => 'Asynchronous JavaScript: Promises & Async/Await',
                'description' => 'Master asynchronous programming in JavaScript. Understand callbacks, promises, async/await, error handling, and parallel execution. Learn to work with APIs, handle race conditions, and build responsive applications that don\'t block the UI.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreetAndDirt.mp4',
                'duration_seconds' => 840,
                'is_published' => true,
                'resources' => ['Async Patterns Guide', 'API Examples', 'Error Handling Strategies'],
            ];
            $baseLessons[] = [
                'title' => 'ES6+ Features & Modern JavaScript',
                'description' => 'Deep dive into modern JavaScript features: arrow functions, destructuring, spread/rest operators, template literals, modules, classes, and more. Learn functional programming concepts and how to write clean, maintainable code.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4',
                'duration_seconds' => 720,
                'is_published' => true,
                'resources' => ['ES6 Feature List', 'Code Examples', 'Browser Compatibility Chart'],
            ];
        }

        if (str_contains($courseTitle, 'React')) {
            $baseLessons[] = [
                'title' => 'React Hooks & State Management',
                'description' => 'Master React Hooks including useState, useEffect, useContext, useReducer, and custom hooks. Learn state management patterns, context API, and when to use Redux. Build complex UIs with proper state architecture.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WhatCarCanYouGetForAGrand.mp4',
                'duration_seconds' => 960,
                'is_published' => true,
                'resources' => ['Hooks Cheat Sheet', 'State Management Patterns', 'Custom Hooks Library'],
            ];
        }

        if (str_contains($courseTitle, 'Node') || str_contains($courseTitle, 'Full-Stack')) {
            $baseLessons[] = [
                'title' => 'Building RESTful APIs with Express',
                'description' => 'Create production-ready REST APIs with Express.js. Learn routing, middleware, error handling, validation, authentication with JWT, rate limiting, and API documentation. Implement CRUD operations and best practices for API design.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/VolkswagenGTIReview.mp4',
                'duration_seconds' => 1020,
                'is_published' => true,
                'resources' => ['API Design Guide', 'Postman Collection', 'Authentication Templates'],
            ];
        }

        if (str_contains($courseTitle, 'Database')) {
            $baseLessons[] = [
                'title' => 'Query Optimization & Performance Tuning',
                'description' => 'Learn advanced query optimization techniques. Understand indexes, execution plans, query analysis, and performance monitoring. Master techniques to make your database queries lightning fast even with millions of records.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'duration_seconds' => 900,
                'is_published' => true,
                'resources' => ['Optimization Checklist', 'Index Strategy Guide', 'Performance Tools'],
            ];
        }

        return $baseLessons;
    }
}