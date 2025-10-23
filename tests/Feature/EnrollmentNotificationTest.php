<?php

namespace Tests\Feature;

use App\Actions\Enrollment\EnrollInCourseAction;
use App\Actions\Enrollment\EnrollInFreeCourseAction;
use App\Events\EnrollmentCreated;
use App\Models\Course;
use App\Models\User;
use App\Notifications\EnrollmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EnrollmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_event_is_dispatched_on_free_enrollment(): void
    {
        Event::fake([EnrollmentCreated::class]);

        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => null,
            'is_published' => true,
        ]);

        $action = app(EnrollInFreeCourseAction::class);
        $enrollment = $action->execute($user, $course);

        Event::assertDispatched(EnrollmentCreated::class, function ($event) use ($enrollment) {
            return $event->enrollment->id === $enrollment->id;
        });
    }

    public function test_enrollment_event_is_dispatched_on_paid_enrollment(): void
    {
        Event::fake([EnrollmentCreated::class]);

        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => 99.99,
            'is_published' => true,
        ]);

        $action = app(EnrollInCourseAction::class);
        $enrollment = $action->execute($user, $course, [
            'payment_id' => 'stripe_test_123',
        ]);

        Event::assertDispatched(EnrollmentCreated::class, function ($event) use ($enrollment) {
            return $event->enrollment->id === $enrollment->id;
        });
    }

    public function test_enrollment_notification_is_sent_to_user(): void
    {
        Notification::fake();

        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => null,
            'is_published' => true,
        ]);

        $action = app(EnrollInFreeCourseAction::class);
        $enrollment = $action->execute($user, $course);

        // Process the event listener manually since we're not using queues in tests
        $listener = app(\App\Listeners\SendEnrollmentNotification::class);
        $listener->handle(new EnrollmentCreated($enrollment));

        Notification::assertSentTo(
            $user,
            EnrollmentNotification::class,
            function ($notification) use ($enrollment) {
                return $notification->toArray($enrollment->user)['enrollment_id'] === $enrollment->id;
            }
        );
    }

    public function test_enrollment_notification_contains_course_details(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'level' => 'beginner',
            'price' => null,
            'is_published' => true,
        ]);

        $action = app(EnrollInFreeCourseAction::class);
        $enrollment = $action->execute($user, $course);

        $notification = new EnrollmentNotification($enrollment);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Test Course', $mailMessage->subject);
        $this->assertStringContainsString($user->name, $mailMessage->greeting);
    }

    public function test_enrollment_notification_shows_payment_info_for_paid_courses(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'title' => 'Paid Course',
            'price' => 149.99,
            'currency' => 'USD',
            'is_published' => true,
        ]);

        $action = app(EnrollInCourseAction::class);
        $enrollment = $action->execute($user, $course, [
            'payment_id' => 'stripe_test_123',
        ]);

        $notification = new EnrollmentNotification($enrollment);
        $array = $notification->toArray($user);

        $this->assertTrue($array['is_paid']);
        $this->assertEquals(149.99, $array['paid_amount']);
        $this->assertEquals('USD', $array['currency']);
    }

    public function test_enrollment_notification_has_database_channel(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => null,
            'is_published' => true,
        ]);

        $action = app(EnrollInFreeCourseAction::class);
        $enrollment = $action->execute($user, $course);

        $notification = new EnrollmentNotification($enrollment);
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_enrollment_notification_has_action_button(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => null,
            'is_published' => true,
        ]);

        $action = app(EnrollInFreeCourseAction::class);
        $enrollment = $action->execute($user, $course);

        $notification = new EnrollmentNotification($enrollment);
        $mailMessage = $notification->toMail($user);

        $this->assertNotEmpty($mailMessage->actionUrl);
        $this->assertEquals('Start Learning', $mailMessage->actionText);
    }

    public function test_enrollment_notification_is_queued(): void
    {
        $notification = new EnrollmentNotification(
            new \App\Models\Enrollment()
        );

        $this->assertInstanceOf(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            $notification
        );
    }

    public function test_notification_stores_correct_data_in_database(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'title' => 'Database Test Course',
            'slug' => 'database-test-course',
            'price' => 99.99,
            'is_published' => true,
        ]);

        $action = app(EnrollInCourseAction::class);
        $enrollment = $action->execute($user, $course, [
            'payment_id' => 'stripe_test_123',
        ]);

        $notification = new EnrollmentNotification($enrollment);
        $data = $notification->toArray($user);

        $this->assertEquals($enrollment->id, $data['enrollment_id']);
        $this->assertEquals($course->id, $data['course_id']);
        $this->assertEquals('Database Test Course', $data['course_title']);
        $this->assertEquals('database-test-course', $data['course_slug']);
        $this->assertTrue($data['is_paid']);
    }
}
