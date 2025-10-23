<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Enrollment $enrollment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
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
        $course = $this->enrollment->course;
        $isPaid = $this->enrollment->isPaid();

        return (new MailMessage)
            ->subject('Welcome to ' . $course->title . '!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Congratulations! You have successfully enrolled in **' . $course->title . '**.')
            ->when($isPaid, function ($mail) {
                return $mail->line('Thank you for your purchase of ' . $this->enrollment->paid_amount . ' ' . $this->enrollment->currency . '.');
            })
            ->line('You now have access to all course materials and can start learning at your own pace.')
            ->line('**Course Details:**')
            ->line('• Level: ' . ucfirst($course->level))
            ->line('• Lessons: ' . $course->publishedLessons->count())
            ->action('Start Learning', route('courses.show', $course))
            ->line('Happy learning!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'enrollment_id' => $this->enrollment->id,
            'course_id' => $this->enrollment->course_id,
            'course_title' => $this->enrollment->course->title,
            'course_slug' => $this->enrollment->course->slug,
            'is_paid' => $this->enrollment->isPaid(),
            'paid_amount' => $this->enrollment->paid_amount,
            'currency' => $this->enrollment->currency,
            'message' => 'You have successfully enrolled in ' . $this->enrollment->course->title,
        ];
    }
}
