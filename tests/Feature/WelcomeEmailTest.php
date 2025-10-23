<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_email_is_sent_when_user_registers()
    {
        Event::fake([Registered::class]);

        // Register a new user
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $response->assertRedirect('/dashboard');

        // Assert user was created
        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($user);

        // Assert Registered event was dispatched
        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_welcome_notification_contains_correct_content()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $notification = new WelcomeNotification();
        $mailMessage = $notification->toMail($user);

        $this->assertEquals('Welcome to Mini LMS!', $mailMessage->subject);
        $this->assertStringContainsString('Hello John Doe!', $mailMessage->greeting);
        $this->assertContains('Thank you for registering with Mini LMS - your gateway to quality online learning.', $mailMessage->introLines);
        $this->assertEquals('Browse Courses', $mailMessage->actionText);
        $this->assertEquals(route('courses.index'), $mailMessage->actionUrl);
    }

    public function test_welcome_notification_has_correct_channels()
    {
        $user = User::factory()->create();
        $notification = new WelcomeNotification();

        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_welcome_notification_is_queued()
    {
        $notification = new WelcomeNotification();

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }

    public function test_welcome_notification_stores_data_in_database()
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
        ]);

        $notification = new WelcomeNotification();
        $data = $notification->toArray($user);

        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('welcome', $data['type']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('action_url', $data);
        $this->assertArrayHasKey('action_text', $data);
        $this->assertEquals('Browse Courses', $data['action_text']);
        $this->assertEquals(route('courses.index'), $data['action_url']);
    }
}
