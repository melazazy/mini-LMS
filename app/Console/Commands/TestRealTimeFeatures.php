<?php

namespace App\Console\Commands;

use App\Events\CourseCompleted;
use App\Events\ProgressUpdated;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Notifications\CourseCompletionNotification;
use App\Notifications\CourseUpdateNotification;
use Illuminate\Console\Command;

/**
 * Command to test real-time features including broadcasting and notifications.
 */
class TestRealTimeFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:realtime {action=menu}
                            {--user= : User ID to test with}
                            {--course= : Course ID to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test real-time features: broadcasting, notifications, and push';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        if ($action === 'menu') {
            return $this->showMenu();
        }

        return match ($action) {
            'config' => $this->checkConfiguration(),
            'progress' => $this->testProgressBroadcast(),
            'completion' => $this->testCourseCompletion(),
            'notification' => $this->testNotification(),
            'push' => $this->testPushNotification(),
            'all' => $this->runAllTests(),
            default => $this->error("Unknown action: {$action}"),
        };
    }

    /**
     * Show interactive menu.
     */
    private function showMenu()
    {
        $this->info('🚀 Real-time Features Test Suite');
        $this->newLine();

        $action = $this->choice(
            'What would you like to test?',
            [
                'config' => 'Check Configuration',
                'progress' => 'Test Progress Broadcasting',
                'completion' => 'Test Course Completion',
                'notification' => 'Test Notifications',
                'push' => 'Test Push Notifications',
                'all' => 'Run All Tests',
            ],
            'config'
        );

        $this->newLine();
        return $this->handle();
    }

    /**
     * Check configuration.
     */
    private function checkConfiguration()
    {
        $this->info('📋 Checking Configuration...');
        $this->newLine();

        $checks = [
            'Pusher App Key' => config('broadcasting.connections.pusher.key'),
            'Pusher App Secret' => config('broadcasting.connections.pusher.secret') ? '***' : null,
            'Pusher Cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'Broadcast Driver' => config('broadcasting.default'),
            'Queue Connection' => config('queue.default'),
            'WebPush Public Key' => config('webpush.vapid.public_key') ? 'Set' : 'Not Set',
        ];

        foreach ($checks as $label => $value) {
            if ($value) {
                $this->line("✅ {$label}: <info>{$value}</info>");
            } else {
                $this->line("❌ {$label}: <error>Not configured</error>");
            }
        }

        $this->newLine();
        
        // Check database
        $this->info('Checking Database...');
        $userCount = User::count();
        $courseCount = Course::count();
        $this->line("✅ Users: <info>{$userCount}</info>");
        $this->line("✅ Courses: <info>{$courseCount}</info>");

        return 0;
    }

    /**
     * Test progress broadcasting.
     */
    private function testProgressBroadcast()
    {
        $this->info('📊 Testing Progress Broadcasting...');
        $this->newLine();

        $userId = $this->option('user') ?? $this->ask('Enter User ID', 1);
        $user = User::find($userId);

        if (!$user) {
            $this->error("User {$userId} not found!");
            return 1;
        }

        $course = Course::with('lessons')->first();
        if (!$course || $course->lessons->isEmpty()) {
            $this->error('No course with lessons found!');
            return 1;
        }

        $lesson = $course->lessons->first();

        $this->info("Broadcasting progress for: {$user->name}");
        $this->info("Course: {$course->title}");
        $this->info("Lesson: {$lesson->title}");
        $this->newLine();

        event(new ProgressUpdated($user, $lesson, 75, 450));

        $this->line('✅ Progress event broadcasted!');
        $this->line("📡 Channel: <info>private-user.{$user->id}</info>");
        $this->line("📊 Progress: <info>75%</info>");
        $this->newLine();
        $this->comment('Check your Pusher dashboard or browser console for the event.');

        return 0;
    }

    /**
     * Test course completion.
     */
    private function testCourseCompletion()
    {
        $this->info('🎉 Testing Course Completion...');
        $this->newLine();

        $userId = $this->option('user') ?? $this->ask('Enter User ID', 1);
        $user = User::find($userId);

        if (!$user) {
            $this->error("User {$userId} not found!");
            return 1;
        }

        $courseId = $this->option('course') ?? $this->ask('Enter Course ID', 1);
        $course = Course::find($courseId);

        if (!$course) {
            $this->error("Course {$courseId} not found!");
            return 1;
        }

        $this->info("Triggering completion for: {$user->name}");
        $this->info("Course: {$course->title}");
        $this->newLine();

        event(new CourseCompleted($user, $course));

        $this->line('✅ Course completion event fired!');
        $this->line("📡 Broadcast to: <info>private-user.{$user->id}</info>");
        $this->line("📧 Notification queued for: <info>{$user->email}</info>");
        $this->newLine();
        $this->comment('Check queue worker logs and Pusher dashboard.');

        return 0;
    }

    /**
     * Test notification sending.
     */
    private function testNotification()
    {
        $this->info('📧 Testing Notifications...');
        $this->newLine();

        $userId = $this->option('user') ?? $this->ask('Enter User ID', 1);
        $user = User::find($userId);

        if (!$user) {
            $this->error("User {$userId} not found!");
            return 1;
        }

        $course = Course::first();
        if (!$course) {
            $this->error('No course found!');
            return 1;
        }

        $type = $this->choice(
            'Which notification?',
            ['completion' => 'Course Completion', 'update' => 'Course Update'],
            'update'
        );

        $this->info("Sending {$type} notification to: {$user->name}");
        $this->newLine();

        if ($type === 'completion') {
            $user->notify(new CourseCompletionNotification($course));
            $this->line('✅ Course Completion notification sent!');
        } else {
            $user->notify(new CourseUpdateNotification($course, 'updated'));
            $this->line('✅ Course Update notification sent!');
        }

        $this->newLine();
        $this->comment('Channels: Mail, WebPush, Database');
        $this->comment('Check queue worker logs for processing.');

        return 0;
    }

    /**
     * Test push notification.
     */
    private function testPushNotification()
    {
        $this->info('🔔 Testing Push Notifications...');
        $this->newLine();

        $userId = $this->option('user') ?? $this->ask('Enter User ID', 1);
        $user = User::find($userId);

        if (!$user) {
            $this->error("User {$userId} not found!");
            return 1;
        }

        $subscriptionCount = $user->pushSubscriptions()->count();
        
        if ($subscriptionCount === 0) {
            $this->warn("⚠️  User has no push subscriptions!");
            $this->comment('Subscribe via the PushNotificationManager component first.');
            return 1;
        }

        $this->info("User has {$subscriptionCount} push subscription(s)");
        $this->newLine();

        $course = Course::first();
        $user->notify(new CourseUpdateNotification($course, 'new content'));

        $this->line('✅ Push notification queued!');
        $this->newLine();
        $this->comment('Check browser for push notification.');
        $this->comment('Note: Requires HTTPS in production.');

        return 0;
    }

    /**
     * Run all tests.
     */
    private function runAllTests()
    {
        $this->info('🧪 Running All Tests...');
        $this->newLine();

        $this->checkConfiguration();
        $this->newLine(2);
        
        $this->testProgressBroadcast();
        $this->newLine(2);
        
        $this->testCourseCompletion();
        $this->newLine(2);
        
        $this->testNotification();
        $this->newLine(2);

        $this->info('✅ All tests completed!');
        
        return 0;
    }
}
