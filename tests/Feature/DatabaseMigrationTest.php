<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Step 2: Verify all database tables exist with correct structure
     */
    public function test_users_table_has_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        
        $this->assertTrue(Schema::hasColumns('users', [
            'id',
            'name',
            'email',
            'password',
            'role',
            'email_verified_at',
            'remember_token',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_courses_table_has_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('courses'));
        
        $this->assertTrue(Schema::hasColumns('courses', [
            'id',
            'title',
            'slug',
            'description',
            'level',
            'price',
            'currency',
            'is_published',
            'published_at',
            'thumbnail_url',
            'created_by',
            'free_lesson_count',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_lessons_table_has_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('lessons'));
        
        $this->assertTrue(Schema::hasColumns('lessons', [
            'id',
            'course_id',
            'title',
            'slug',
            'video_url',
            'hls_manifest_url',
            'duration_seconds',
            'is_published',
            'order',
            'is_free_preview',
            'resources',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_enrollments_table_has_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('enrollments'));
        
        $this->assertTrue(Schema::hasColumns('enrollments', [
            'id',
            'user_id',
            'course_id',
            'status',
            'paid_amount',
            'currency',
            'payment_id',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_lesson_progress_table_has_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('lesson_progress'));
        
        $this->assertTrue(Schema::hasColumns('lesson_progress', [
            'id',
            'user_id',
            'lesson_id',
            'watched_percentage',
            'last_position_seconds',
            'last_watched_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_course_completions_table_has_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('course_completions'));
        
        $this->assertTrue(Schema::hasColumns('course_completions', [
            'id',
            'user_id',
            'course_id',
            'completed_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_moderation_reviews_table_has_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('moderation_reviews'));
        
        $this->assertTrue(Schema::hasColumns('moderation_reviews', [
            'id',
            'subject_type',
            'subject_id',
            'state',
            'reviewer_id',
            'submitted_by',
            'notes',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_notifications_table_has_correct_structure(): void
    {
        $this->assertTrue(Schema::hasTable('notifications'));
        
        $this->assertTrue(Schema::hasColumns('notifications', [
            'id',
            'user_id',
            'type',
            'data',
            'read_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_jobs_table_exists_for_queue(): void
    {
        $this->assertTrue(Schema::hasTable('jobs'));
        
        $this->assertTrue(Schema::hasColumns('jobs', [
            'id',
            'queue',
            'payload',
            'attempts',
            'reserved_at',
            'available_at',
            'created_at',
        ]));
    }

    public function test_cache_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('cache'));
        
        $this->assertTrue(Schema::hasColumns('cache', [
            'key',
            'value',
            'expiration',
        ]));
    }
}
