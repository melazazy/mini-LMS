<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Notification sent when a user completes a course.
 * Supports both email and web push channels.
 */
class CourseCompletionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Course $course
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', WebPushChannel::class, 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Congratulations! You completed ' . $this->course->title)
            ->line('Congratulations! You have successfully completed the course "' . $this->course->title . '".')
            ->line('Your dedication and hard work have paid off!')
            ->action('View Course', route('courses.show', $this->course))
            ->line('Keep up the great work and continue your learning journey!');
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Course Completed!')
            ->icon('/favicon.ico')
            ->body('Congratulations! You completed "' . $this->course->title . '".')
            ->action('View Course', 'view_course')
            ->data([
                'url' => route('courses.show', $this->course),
                'course_id' => $this->course->id,
                'type' => 'completion',
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'type' => 'completion',
            'url' => route('courses.show', $this->course),
        ];
    }
}
