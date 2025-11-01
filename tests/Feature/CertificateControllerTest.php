<?php

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('student can download their own certificate', function () {
    config(['filesystems.default' => 'local']);
    Storage::fake('local');
    
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->approved()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
        'pdf_path' => 'certificates/test.pdf',
    ]);

    // Create fake file
    Storage::put($certificate->pdf_path, 'fake pdf content');

    $response = $this->actingAs($student)
        ->get(route('certificates.download', ['certificate' => $certificate->id, 'format' => 'pdf']));

    $response->assertOk();
});

test('student cannot download another students certificate', function () {
    $student1 = User::factory()->student()->create();
    $student2 = User::factory()->student()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student1->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->approved()->create([
        'user_id' => $student1->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
    ]);

    $response = $this->actingAs($student2)
        ->get(route('certificates.download', ['certificate' => $certificate->id, 'format' => 'pdf']));

    $response->assertForbidden();
});

test('admin can download any certificate', function () {
    config(['filesystems.default' => 'local']);
    Storage::fake('local');
    
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->approved()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
        'pdf_path' => 'certificates/test.pdf',
    ]);

    Storage::put($certificate->pdf_path, 'fake pdf content');

    $response = $this->actingAs($admin)
        ->get(route('certificates.download', ['certificate' => $certificate->id, 'format' => 'pdf']));

    $response->assertOk();
});

test('course instructor can download their course certificates', function () {
    config(['filesystems.default' => 'local']);
    Storage::fake('local');
    
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->create(['created_by' => $instructor->id]);
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->approved()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
        'pdf_path' => 'certificates/test.pdf',
    ]);

    Storage::put($certificate->pdf_path, 'fake pdf content');

    $response = $this->actingAs($instructor)
        ->get(route('certificates.download', ['certificate' => $certificate->id, 'format' => 'pdf']));

    $response->assertOk();
});

test('pending certificate cannot be downloaded', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();
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

    $response = $this->actingAs($student)
        ->get(route('certificates.download', ['certificate' => $certificate->id, 'format' => 'pdf']));

    $response->assertForbidden();
});

test('certificate verification page shows valid certificate', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->approved()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
    ]);

    $response = $this->get(route('certificates.verify', $certificate->verification_hash));

    $response->assertOk()
        ->assertSee($certificate->certificate_number)
        ->assertSee($student->name)
        ->assertSee($course->title)
        ->assertSee('Certificate Verified');
});

test('certificate verification page shows not found for invalid hash', function () {
    $response = $this->get(route('certificates.verify', 'invalid-hash'));

    $response->assertOk()
        ->assertSee('Certificate Not Found');
});

test('revoked certificate shows revocation notice on verification page', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->revoked()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
        'revocation_reason' => 'Test revocation',
    ]);

    $response = $this->get(route('certificates.verify', $certificate->verification_hash));

    $response->assertOk()
        ->assertSee('Certificate Revoked')
        ->assertSee('Test revocation');
});

test('guest cannot download certificate', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->approved()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
    ]);

    $response = $this->get(route('certificates.download', ['certificate' => $certificate->id, 'format' => 'pdf']));

    $response->assertRedirect(route('login'));
});

test('guest can view certificate verification page', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $certificate = Certificate::factory()->approved()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_id' => $enrollment->id,
    ]);

    $response = $this->get(route('certificates.verify', $certificate->verification_hash));

    $response->assertOk();
});
