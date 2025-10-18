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
        $baseLessons = [
            [
                'title' => 'Introduction and Overview',
                'video_url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
                'duration_seconds' => 300,
                'is_published' => true,
                'resources' => ['PDF Guide', 'Code Examples'],
            ],
            [
                'title' => 'Setting Up Your Environment',
                'video_url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4',
                'duration_seconds' => 450,
                'is_published' => true,
                'resources' => ['Installation Guide', 'Troubleshooting Tips'],
            ],
            [
                'title' => 'Core Concepts and Fundamentals',
                'video_url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
                'duration_seconds' => 600,
                'is_published' => true,
                'resources' => ['Cheat Sheet', 'Practice Exercises'],
            ],
            [
                'title' => 'Hands-on Practice Session',
                'video_url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4',
                'duration_seconds' => 900,
                'is_published' => true,
                'resources' => ['Project Files', 'Solution Code'],
            ],
            [
                'title' => 'Advanced Topics and Best Practices',
                'video_url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
                'duration_seconds' => 750,
                'is_published' => true,
                'resources' => ['Best Practices Guide', 'Common Pitfalls'],
            ],
            [
                'title' => 'Real-world Project Implementation',
                'video_url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4',
                'duration_seconds' => 1200,
                'is_published' => true,
                'resources' => ['Project Requirements', 'Final Code'],
            ],
        ];

        // Add course-specific lessons
        if (str_contains($courseTitle, 'Laravel')) {
            $baseLessons[] = [
                'title' => 'Laravel Authentication and Authorization',
                'video_url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
                'duration_seconds' => 800,
                'is_published' => true,
                'resources' => ['Auth Package', 'Middleware Examples'],
            ];
        }

        return $baseLessons;
    }
}