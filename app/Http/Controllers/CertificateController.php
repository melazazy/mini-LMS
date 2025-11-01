<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Certificate Controller
 * 
 * Handles certificate download and public verification.
 */
class CertificateController extends Controller
{
    /**
     * Download certificate file.
     * 
     * Note: Only PDF format is supported. PNG requires ImageMagick.
     *
     * @param Certificate $certificate
     * @param string $format
     * @return StreamedResponse
     */
    public function download(Certificate $certificate, string $format = 'pdf')
    {
        // Check if user is authorized to download
        if (!$this->canDownload($certificate)) {
            abort(403, 'You are not authorized to download this certificate.');
        }

        // Check if certificate is approved
        if (!$certificate->isApproved()) {
            abort(403, 'This certificate has not been approved yet.');
        }

        // Only PDF format is supported
        if ($format !== 'pdf') {
            abort(400, 'Only PDF format is supported. PNG generation requires ImageMagick.');
        }

        // Get PDF path
        $path = $certificate->pdf_path;

        if (!$path || !Storage::exists($path)) {
            abort(404, 'Certificate file not found.');
        }

        // Generate filename
        $filename = 'Certificate_' . $certificate->certificate_number . '.pdf';

        return Storage::download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Verify certificate by hash.
     *
     * @param string $hash
     * @return \Illuminate\View\View
     */
    public function verify(string $hash)
    {
        $certificate = Certificate::where('verification_hash', $hash)->first();

        if (!$certificate) {
            return view('certificates.verify', [
                'found' => false,
                'message' => 'Certificate not found. Please check the verification code and try again.',
            ]);
        }

        // Load relationships
        $certificate->load(['user', 'course', 'issuer']);

        return view('certificates.verify', [
            'found' => true,
            'certificate' => $certificate,
        ]);
    }

    /**
     * Display demo certificate for testing layout.
     *
     * @return \Illuminate\View\View
     */
    public function demo()
    {
        // Create a mock certificate object for demo purposes
        $demoCertificate = new Certificate([
            'certificate_number' => 'CERT-DEMO-2024-0001',
            'verification_hash' => 'demo-hash-' . uniqid(),
            'issued_at' => now(),
            'status' => 'approved',
        ]);

        // Create mock user
        $demoUser = new User([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        // Create mock course
        $demoCourse = new Course([
            'title' => 'Advanced Web Development with Laravel',
            'level' => 'intermediate',
        ]);

        // Create mock instructor
        $demoInstructor = new User([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
        ]);

        // Set relationships
        $demoCertificate->setRelation('user', $demoUser);
        $demoCertificate->setRelation('course', $demoCourse);
        $demoCourse->setRelation('creator', $demoInstructor);
        $demoCertificate->setRelation('issuer', $demoInstructor);

        // Generate demo QR code (simple SVG)
        $qrCodeSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
            <rect width="100" height="100" fill="white"/>
            <text x="50" y="50" text-anchor="middle" font-size="12" fill="black">DEMO QR</text>
        </svg>';
        $qrCodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);

        return view('certificates.template', [
            'certificate' => $demoCertificate,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);
    }

    /**
     * Check if current user can download the certificate.
     *
     * @param Certificate $certificate
     * @return bool
     */
    protected function canDownload(Certificate $certificate): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Owner can download
        if ($certificate->user_id === $user->id) {
            return true;
        }

        // Admin can download
        if ($user->isAdmin()) {
            return true;
        }

        // Course instructor can download
        if ($certificate->course->created_by === $user->id) {
            return true;
        }

        return false;
    }
}
