<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Mini LMS!')
            ->greeting('Hello ' . $notifiable->name . '! 👋')
            ->line('Thank you for registering with Mini LMS - your gateway to quality online learning.')
            ->line('We\'re excited to have you join our community of learners!')
            ->line('**Get Started:**')
            ->line('• Browse our course catalog')
            ->line('• Enroll in free courses instantly')
            ->line('• Track your learning progress')
            ->line('• Earn certificates upon completion')
            ->action('Browse Courses', route('courses.index'))
            ->line('If you have any questions, feel free to reach out to our support team.')
            ->line('Happy learning! 🚀');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'message' => 'Welcome to Mini LMS! Start your learning journey today.',
            'action_url' => route('courses.index'),
            'action_text' => 'Browse Courses',
        ];
    }
}
