<?php

namespace App\Listeners;

use App\Events\CertificateIssued;
use App\Notifications\CertificateIssuedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Certificate Issued Notification Listener
 * 
 * Listens for CertificateIssued event and sends notification.
 * Synchronous for development (remove ShouldQueue), use ShouldQueue for production.
 */
class SendCertificateIssuedNotification
{
    // Removed ShouldQueue and InteractsWithQueue for synchronous sending in development
    // For production: add "implements ShouldQueue" and "use InteractsWithQueue;"
    
    // Removed for synchronous mode
    // public $tries = 3;

    /**
     * Handle the event.
     *
     * @param CertificateIssued $event
     * @return void
     */
    public function handle(CertificateIssued $event): void
    {
        try {
            $certificate = $event->certificate;

            Log::info('Sending certificate issued notification', [
                'certificate_id' => $certificate->id,
                'user_id' => $certificate->user_id,
            ]);

            // Send notification to the user
            $certificate->user->notify(new CertificateIssuedNotification($certificate));

            Log::info('Certificate issued notification sent successfully', [
                'certificate_id' => $certificate->id,
                'user_id' => $certificate->user_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send certificate issued notification', [
                'certificate_id' => $event->certificate->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param CertificateIssued $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(CertificateIssued $event, \Throwable $exception): void
    {
        Log::error('Certificate issued notification failed permanently', [
            'certificate_id' => $event->certificate->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
