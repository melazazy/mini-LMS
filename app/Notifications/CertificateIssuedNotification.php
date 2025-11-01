<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * Certificate Issued Notification
 * 
 * Sends email notification with certificate attachment when approved.
 * Synchronous for development (remove ShouldQueue), use ShouldQueue for production.
 */
class CertificateIssuedNotification extends Notification
{
    // Removed Queueable trait for synchronous sending in development
    // For production: add "implements ShouldQueue" and "use Queueable;"

    /**
     * The certificate instance.
     *
     * @var Certificate
     */
    protected Certificate $certificate;

    /**
     * Create a new notification instance.
     *
     * @param Certificate $certificate
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
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
        $mailMessage = (new MailMessage)
            ->subject('🎉 Your Certificate is Ready!')
            ->greeting('Congratulations, ' . $notifiable->name . '!')
            ->line('Your certificate for completing **' . $this->certificate->course->title . '** has been issued.')
            ->line('You have successfully demonstrated mastery of the course material.')
            ->line('**Certificate Details:**')
            ->line('- Certificate Number: ' . $this->certificate->certificate_number)
            ->line('- Course: ' . $this->certificate->course->title)
            ->line('- Level: ' . ucfirst($this->certificate->course->level))
            ->line('- Issued Date: ' . $this->certificate->issued_at->format('F d, Y'))
            ->action('Download Certificate', route('certificates.download', [
                'certificate' => $this->certificate->id,
                'format' => 'pdf'
            ]))
            ->line('You can also verify your certificate at any time using the link below:')
            ->action('Verify Certificate', $this->certificate->verification_url)
            ->line('Share your achievement with your network and showcase your new skills!')
            ->line('Thank you for learning with us!');

        // Attach PDF certificate if it exists
        if ($this->certificate->pdf_path && Storage::exists($this->certificate->pdf_path)) {
            $mailMessage->attach(Storage::path($this->certificate->pdf_path), [
                'as' => 'Certificate_' . $this->certificate->certificate_number . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'certificate_id' => $this->certificate->id,
            'certificate_number' => $this->certificate->certificate_number,
            'course_id' => $this->certificate->course_id,
            'course_title' => $this->certificate->course->title,
            'issued_at' => $this->certificate->issued_at->toDateTimeString(),
            'message' => 'Your certificate for ' . $this->certificate->course->title . ' has been issued!',
        ];
    }
}
