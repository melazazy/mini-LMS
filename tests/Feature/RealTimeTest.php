<?php

namespace Tests\Feature;

use App\Events\CourseCompleted;
use App\Events\ProgressUpdated;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\PushSubscription;
use App\Models\User;
use App\Notifications\CourseCompletionNotification;
use App\Notifications\CourseUpdateNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Test suite for real-time features including broadcasting and push notifications.
 */
class RealTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that ProgressUpdated event is broadcasted correctly.
     */
    public function test_progress_updated_event_is_broadcasted(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        event(new ProgressUpdated($user, $lesson, 50, 300));

        Event::assertDispatched(ProgressUpdated::class, function ($event) use ($user, $lesson) {
            return $event->user->id === $user->id
                && $event->lesson->id === $lesson->id
                && $event->percentage === 50
                && $event->position === 300;
        });
    }

    /**
     * Test that ProgressUpdated event broadcasts to correct channel.
     */
    public function test_progress_updated_broadcasts_to_user_channel(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $event = new ProgressUpdated($user, $lesson, 75, 450);
        
        $channel = $event->broadcastOn();
        
        $this->assertEquals('private-user.' . $user->id, $channel->name);
    }

    /**
     * Test that ProgressUpdated event broadcasts correct data.
     */
    public function test_progress_updated_broadcasts_correct_data(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $event = new ProgressUpdated($user, $lesson, 90, 540);
        $data = $event->broadcastWith();

        $this->assertArrayHasKey('lesson_id', $data);
        $this->assertArrayHasKey('course_id', $data);
        $this->assertArrayHasKey('percentage', $data);
        $this->assertArrayHasKey('position', $data);
        $this->assertArrayHasKey('updated_at', $data);
        
        $this->assertEquals($lesson->id, $data['lesson_id']);
        $this->assertEquals($course->id, $data['course_id']);
        $this->assertEquals(90, $data['percentage']);
        $this->assertEquals(540, $data['position']);
    }

    /**
     * Test that CourseCompleted event sends notification.
     */
    public function test_course_completed_event_sends_notification(): void
    {
        // Only fake broadcasting, not the entire event system
        // This allows listeners to run while preventing actual broadcasts
        Bus::fake();
        Notification::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);

        event(new CourseCompleted($user, $course));

        Notification::assertSentTo(
            $user,
            CourseCompletionNotification::class,
            function ($notification) use ($course) {
                return $notification->course->id === $course->id;
            }
        );
    }

    /**
     * Test that CourseCompleted event broadcasts to user channel.
     */
    public function test_course_completed_broadcasts_to_user_channel(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);

        $event = new CourseCompleted($user, $course);
        $channel = $event->broadcastOn();

        $this->assertEquals('private-user.' . $user->id, $channel->name);
    }

    /**
     * Test that CourseCompletionNotification has correct channels.
     */
    public function test_course_completion_notification_has_correct_channels(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);

        $notification = new CourseCompletionNotification($course);
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    /**
     * Test that CourseUpdateNotification has correct channels.
     */
    public function test_course_update_notification_has_correct_channels(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_published' => true]);

        $notification = new CourseUpdateNotification($course, 'updated');
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    /**
     * Test that push subscription can be created.
     */
    public function test_push_subscription_can_be_created(): void
    {
        $user = User::factory()->create();

        $subscription = PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
            'public_key' => 'test-public-key',
            'auth_token' => 'test-auth-token',
        ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        ]);

        $this->assertEquals($user->id, $subscription->user->id);
    }

    /**
     * Test that duplicate push subscriptions are prevented.
     */
    public function test_duplicate_push_subscriptions_are_prevented(): void
    {
        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/test-endpoint';

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => $endpoint,
            'public_key' => 'test-public-key-1',
            'auth_token' => 'test-auth-token-1',
        ]);

        // Update or create should update existing subscription
        PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $endpoint,
            ],
            [
                'public_key' => 'test-public-key-2',
                'auth_token' => 'test-auth-token-2',
            ]
        );

        $this->assertEquals(1, PushSubscription::where('user_id', $user->id)->count());
        
        $subscription = PushSubscription::where('user_id', $user->id)->first();
        $this->assertEquals('test-public-key-2', $subscription->public_key);
    }

    /**
     * Test that push subscriptions are deleted when user is deleted.
     */
    public function test_push_subscriptions_deleted_with_user(): void
    {
        $user = User::factory()->create();

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
            'public_key' => 'test-public-key',
            'auth_token' => 'test-auth-token',
        ]);

        $this->assertEquals(1, PushSubscription::where('user_id', $user->id)->count());

        $user->delete();

        $this->assertEquals(0, PushSubscription::where('user_id', $user->id)->count());
    }

    /**
     * Test that CourseCompletionNotification generates correct mail content.
     */
    public function test_course_completion_notification_mail_content(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'title' => 'Test Course',
            'is_published' => true,
        ]);

        $notification = new CourseCompletionNotification($course);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Congratulations', $mail->subject);
        $this->assertStringContainsString('Test Course', $mail->subject);
    }

    /**
     * Test that CourseUpdateNotification generates correct mail content.
     */
    public function test_course_update_notification_mail_content(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'title' => 'Updated Course',
            'is_published' => true,
        ]);

        $notification = new CourseUpdateNotification($course, 'updated');
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Course Update', $mail->subject);
        $this->assertStringContainsString('Updated Course', $mail->subject);
    }

    /**
     * Test that notifications are queued for async processing.
     */
    public function test_notifications_are_queued(): void
    {
        $course = Course::factory()->create(['is_published' => true]);

        $notification = new CourseCompletionNotification($course);
        
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }
}
