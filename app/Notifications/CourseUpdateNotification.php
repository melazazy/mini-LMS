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
 * Notification sent when a course is updated.
 * Supports both email and web push channels.
 */
class CourseUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Course $course,
        public string $type = 'updated'
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
            ->subject('Course Update: ' . $this->course->title)
            ->line('The course "' . $this->course->title . '" has been ' . $this->type . '.')
            ->action('View Course', route('courses.show', $this->course))
            ->line('Thank you for learning with us!');
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Course ' . ucfirst($this->type))
            ->icon('/favicon.ico')
            ->body('The course "' . $this->course->title . '" has been ' . $this->type . '.')
            ->action('View Course', 'view_course')
            ->data([
                'url' => route('courses.show', $this->course),
                'course_id' => $this->course->id,
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
            'type' => $this->type,
            'url' => route('courses.show', $this->course),
        ];
    }
}
