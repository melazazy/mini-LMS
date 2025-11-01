<?php

use App\Actions\Certificate\GenerateCertificateAction;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('certificate can be created for eligible enrollment', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    // Create lessons and mark as completed
    $lessons = Lesson::factory()->count(5)->create([
        'course_id' => $course->id,
        'is_published' => true,
    ]);

    foreach ($lessons as $lesson) {
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 100,
            'last_position_seconds' => 0,
        ]);
    }

    $action = new GenerateCertificateAction();
    $certificate = $action->createCertificateRequest($enrollment);

    expect($certificate)->not->toBeNull()
        ->and($certificate->user_id)->toBe($student->id)
        ->and($certificate->course_id)->toBe($course->id)
        ->and($certificate->enrollment_id)->toBe($enrollment->id)
        ->and($certificate->status)->toBe('pending')
        ->and($certificate->certificate_number)->not->toBeEmpty()
        ->and($certificate->verification_hash)->not->toBeEmpty();
});

test('certificate cannot be created for ineligible enrollment', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    // Create lessons but don't complete them
    Lesson::factory()->count(5)->create([
        'course_id' => $course->id,
        'is_published' => true,
    ]);

    $action = new GenerateCertificateAction();
    $certificate = $action->createCertificateRequest($enrollment);

    expect($certificate)->toBeNull();
});

test('certificate eligibility checks completion threshold', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    // Create 10 lessons
    $lessons = Lesson::factory()->count(10)->create([
        'course_id' => $course->id,
        'is_published' => true,
    ]);

    // Complete only 8 lessons (80% - below 90% threshold)
    foreach ($lessons->take(8) as $lesson) {
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 100,
            'last_position_seconds' => 0,
        ]);
    }

    $action = new GenerateCertificateAction();
    
    expect($action->isEligible($enrollment))->toBeFalse();

    // Complete 2 more lessons (100%)
    foreach ($lessons->skip(8) as $lesson) {
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 100,
            'last_position_seconds' => 0,
        ]);
    }

    expect($action->isEligible($enrollment->fresh()))->toBeTrue();
});

test('certificate generation creates PDF file', function () {
    config(['filesystems.default' => 'local']);
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
        'status' => 'pending',
    ]);

    $action = new GenerateCertificateAction();
    $result = $action->execute($certificate);

    expect($result->pdf_path)->not->toBeNull();
    Storage::assertExists($result->pdf_path);
});

test('certificate number is unique', function () {
    $cert1 = Certificate::factory()->create();
    $cert2 = Certificate::factory()->create();

    expect($cert1->certificate_number)->not->toBe($cert2->certificate_number);
});

test('verification hash is unique', function () {
    $cert1 = Certificate::factory()->create();
    $cert2 = Certificate::factory()->create();

    expect($cert1->verification_hash)->not->toBe($cert2->verification_hash);
});

test('duplicate certificate request returns existing certificate', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    // Create lessons and complete them
    $lessons = Lesson::factory()->count(5)->create([
        'course_id' => $course->id,
        'is_published' => true,
    ]);

    foreach ($lessons as $lesson) {
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'watched_percentage' => 100,
            'last_position_seconds' => 0,
        ]);
    }

    $action = new GenerateCertificateAction();
    $cert1 = $action->createCertificateRequest($enrollment);
    $cert2 = $action->createCertificateRequest($enrollment);

    expect($cert1->id)->toBe($cert2->id);
});
