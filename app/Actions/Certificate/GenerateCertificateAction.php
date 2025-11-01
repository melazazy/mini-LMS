<?php

namespace App\Actions\Certificate;

use App\Models\Certificate;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Generate Certificate Action
 * 
 * Generates PDF and PNG certificates for course completion.
 * Follows Action Pattern for business logic encapsulation.
 */
class GenerateCertificateAction
{
    /**
     * Execute the action to generate certificate files.
     *
     * @param Certificate $certificate
     * @return Certificate
     * @throws \Exception
     */
    public function execute(Certificate $certificate): Certificate
    {
        try {
            Log::info('Starting certificate generation', [
                'certificate_id' => $certificate->id,
                'user_id' => $certificate->user_id,
                'course_id' => $certificate->course_id,
            ]);

            // Load relationships
            $certificate->load(['user', 'course.creator', 'issuer']);

            // Generate QR code for verification
            $qrCodeDataUri = $this->generateQrCode($certificate);

            // Generate PDF
            $pdfPath = $this->generatePdf($certificate, $qrCodeDataUri);

            // Generate PNG from PDF
            $pngPath = $this->generatePng($certificate, $qrCodeDataUri);

            // Update certificate with file paths
            $certificate->update([
                'pdf_path' => $pdfPath,
                'png_path' => $pngPath,
            ]);

            Log::info('Certificate generation completed successfully', [
                'certificate_id' => $certificate->id,
                'pdf_path' => $pdfPath,
                'png_path' => $pngPath,
            ]);

            return $certificate->fresh();

        } catch (\Exception $e) {
            Log::error('Certificate generation failed', [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate QR code for certificate verification.
     *
     * @param Certificate $certificate
     * @return string Base64 encoded data URI
     */
    protected function generateQrCode(Certificate $certificate): string
    {
        $verificationUrl = route('certificates.verify', $certificate->verification_hash);

        try {
            // Try to generate QR code as PNG data URI
            $qrCode = QrCode::format('png')
                ->size(200)
                ->margin(0)
                ->generate($verificationUrl);

            return 'data:image/png;base64,' . base64_encode($qrCode);
        } catch (\Exception $e) {
            // Fallback to SVG if imagick is not available
            Log::warning('QR code generation failed, using SVG fallback', [
                'error' => $e->getMessage(),
            ]);

            $qrCode = QrCode::format('svg')
                ->size(200)
                ->margin(0)
                ->generate($verificationUrl);

            return 'data:image/svg+xml;base64,' . base64_encode($qrCode);
        }
    }

    /**
     * Generate PDF certificate.
     *
     * @param Certificate $certificate
     * @param string $qrCodeDataUri
     * @return string Storage path
     */
protected function generatePdf(Certificate $certificate, string $qrCodeDataUri): string
{
    $html = view('certificates.template', [
        'certificate' => $certificate,
        'qrCodeDataUri' => $qrCodeDataUri,
    ])->render();
    
    $pdf = Pdf::loadHTML($html)
        ->setPaper('A4', 'landscape')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);
    
    $filename = 'certificate_' . $certificate->certificate_number . '.pdf';
    $path = 'certificates/' . $certificate->user_id . '/' . $filename;
    
    Storage::put($path, $pdf->output());
    
    return $path;
}

    /**
     * Generate PNG certificate from HTML.
     * 
     * Note: PNG generation requires ImageMagick or similar image processing library.
     * For now, we return null and only use PDF format.
     *
     * @param Certificate $certificate
     * @param string $qrCodeDataUri
     * @return string|null Storage path or null if not generated
     */
    protected function generatePng(Certificate $certificate, string $qrCodeDataUri): ?string
    {
        // PNG generation requires ImageMagick extension
        // Since it's not available, we'll skip PNG generation
        // and only provide PDF format
        
        Log::info('PNG generation skipped (requires ImageMagick)', [
            'certificate_id' => $certificate->id,
        ]);
        
        // Return null - PNG format not available
        return null;

        Log::info('PNG certificate generated (PDF format)', [
            'certificate_id' => $certificate->id,
            'path' => $path,
            'note' => 'PNG is PDF format - requires ImageMagick for true PNG conversion',
        ]);

        return $path;
    }

    /**
     * Check if enrollment is eligible for certificate.
     *
     * @param Enrollment $enrollment
     * @return bool
     */
    public function isEligible(Enrollment $enrollment): bool
    {
        // Check if enrollment is active
        if (!$enrollment->isActive()) {
            return false;
        }

        // Check if course is published
        if (!$enrollment->course->is_published) {
            return false;
        }

        // Check completion percentage (90% threshold)
        $completionPercentage = $enrollment->getCompletionPercentage();
        
        return $completionPercentage >= 90;
    }

    /**
     * Create certificate request for eligible enrollment.
     *
     * @param Enrollment $enrollment
     * @return Certificate|null
     */
    public function createCertificateRequest(Enrollment $enrollment): ?Certificate
    {
        if (!$this->isEligible($enrollment)) {
            Log::warning('Enrollment not eligible for certificate', [
                'enrollment_id' => $enrollment->id,
                'completion' => $enrollment->getCompletionPercentage(),
            ]);
            return null;
        }

        // Check if certificate already exists
        $existing = Certificate::where('enrollment_id', $enrollment->id)->first();
        if ($existing) {
            Log::info('Certificate already exists', [
                'certificate_id' => $existing->id,
                'enrollment_id' => $enrollment->id,
            ]);
            return $existing;
        }

        // Create pending certificate
        $certificate = Certificate::create([
            'user_id' => $enrollment->user_id,
            'course_id' => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'status' => 'pending',
        ]);

        Log::info('Certificate request created', [
            'certificate_id' => $certificate->id,
            'enrollment_id' => $enrollment->id,
        ]);

        return $certificate;
    }
}
