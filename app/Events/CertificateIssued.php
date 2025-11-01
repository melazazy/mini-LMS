<?php

namespace App\Events;

use App\Models\Certificate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Certificate Issued Event
 * 
 * Fired when a certificate is approved and issued to a student.
 */
class CertificateIssued
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The certificate instance.
     *
     * @var Certificate
     */
    public Certificate $certificate;

    /**
     * Create a new event instance.
     *
     * @param Certificate $certificate
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }
}
