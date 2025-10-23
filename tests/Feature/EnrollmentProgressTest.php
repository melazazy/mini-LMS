<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;

test('enrollment calculates progress percentage correctly', function () {
    // Create a course with 5 published lessons
    $course = Course::factory()->create(['is_published' => true]);
    $lessons = Lesson::factory()->count(5)->create([
        'course_id' => $course->id,
        'is_published' => true,
    ]);

    // Create a student
    $student = User::factory()->student()->create();

    // Create enrollment
    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    // Initially, no progress
    expect($enrollment->getCompletionPercentage())->toBe(0.0);

    // Mark 2 lessons as completed (watched_percentage >= 90)
    LessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lessons[0]->id,
        'watched_percentage' => 100,
    ]);

    LessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lessons[1]->id,
        'watched_percentage' => 95,
    ]);

    // Refresh enrollment to get fresh data
    $enrollment = $enrollment->fresh();

    // Should be 40% (2 out of 5 lessons completed)
    expect($enrollment->getCompletionPercentage())->toBe(40.0);

    // Mark 1 more lesson as in-progress (< 90%)
    LessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lessons[2]->id,
        'watched_percentage' => 50,
    ]);

    $enrollment = $enrollment->fresh();

    // Should still be 40% (only 2 completed)
    expect($enrollment->getCompletionPercentage())->toBe(40.0);

    // Complete all remaining lessons
    LessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lessons[3]->id,
        'watched_percentage' => 90,
    ]);

    LessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lessons[4]->id,
        'watched_percentage' => 100,
    ]);

    // Update the in-progress lesson
    LessonProgress::where('user_id', $student->id)
        ->where('lesson_id', $lessons[2]->id)
        ->update(['watched_percentage' => 100]);

    $enrollment = $enrollment->fresh();

    // Should be 100% (5 out of 5 lessons completed)
    expect($enrollment->getCompletionPercentage())->toBe(100.0);
});

test('enrollment progress is isolated per user', function () {
    // Create a course with 4 published lessons
    $course = Course::factory()->create(['is_published' => true]);
    $lessons = Lesson::factory()->count(4)->create([
        'course_id' => $course->id,
        'is_published' => true,
    ]);

    // Create two students
    $student1 = User::factory()->student()->create();
    $student2 = User::factory()->student()->create();

    // Create enrollments for both
    $enrollment1 = Enrollment::create([
        'user_id' => $student1->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    $enrollment2 = Enrollment::create([
        'user_id' => $student2->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    // Student 1 completes 2 lessons
    LessonProgress::create([
        'user_id' => $student1->id,
        'lesson_id' => $lessons[0]->id,
        'watched_percentage' => 100,
    ]);

    LessonProgress::create([
        'user_id' => $student1->id,
        'lesson_id' => $lessons[1]->id,
        'watched_percentage' => 100,
    ]);

    // Student 2 completes 3 lessons
    LessonProgress::create([
        'user_id' => $student2->id,
        'lesson_id' => $lessons[0]->id,
        'watched_percentage' => 100,
    ]);

    LessonProgress::create([
        'user_id' => $student2->id,
        'lesson_id' => $lessons[1]->id,
        'watched_percentage' => 100,
    ]);

    LessonProgress::create([
        'user_id' => $student2->id,
        'lesson_id' => $lessons[2]->id,
        'watched_percentage' => 100,
    ]);

    // Refresh enrollments
    $enrollment1 = $enrollment1->fresh();
    $enrollment2 = $enrollment2->fresh();

    // Student 1 should have 50% (2/4)
    expect($enrollment1->getCompletionPercentage())->toBe(50.0);

    // Student 2 should have 75% (3/4)
    expect($enrollment2->getCompletionPercentage())->toBe(75.0);

    // Verify no data leakage
    expect($enrollment1->getCompletionPercentage())
        ->not->toBe($enrollment2->getCompletionPercentage());
});

test('enrollment progress only counts published lessons', function () {
    // Create a course with 3 published and 2 unpublished lessons
    $course = Course::factory()->create(['is_published' => true]);
    
    $publishedLessons = Lesson::factory()->count(3)->create([
        'course_id' => $course->id,
        'is_published' => true,
    ]);

    $unpublishedLessons = Lesson::factory()->count(2)->create([
        'course_id' => $course->id,
        'is_published' => false,
    ]);

    $student = User::factory()->student()->create();

    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    // Complete all 3 published lessons
    foreach ($publishedLessons as $lesson) {
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 100,
        ]);
    }

    // Also complete unpublished lessons (shouldn't count)
    foreach ($unpublishedLessons as $lesson) {
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 100,
        ]);
    }

    $enrollment = $enrollment->fresh();

    // Should be 100% based on published lessons only (3/3)
    expect($enrollment->getCompletionPercentage())->toBe(100.0);
});

test('enrollment progress handles course with no lessons', function () {
    // Create a course with no lessons
    $course = Course::factory()->create(['is_published' => true]);
    $student = User::factory()->student()->create();

    $enrollment = Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    // Should return 0% for course with no lessons
    expect($enrollment->getCompletionPercentage())->toBe(0.0);
});
