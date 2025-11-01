<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Certificate Model
 * 
 * Represents a course completion certificate issued to a student.
 * Supports manual approval workflow and certificate revocation.
 */
class Certificate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_id',
        'certificate_number',
        'verification_hash',
        'pdf_path',
        'png_path',
        'status',
        'issued_at',
        'issued_by',
        'revocation_reason',
        'revoked_at',
        'revoked_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issued_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = self::generateCertificateNumber();
            }
            if (empty($certificate->verification_hash)) {
                $certificate->verification_hash = self::generateVerificationHash();
            }
        });
    }

    /**
     * Get the user who received the certificate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course for this certificate.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the enrollment associated with this certificate.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the user who issued the certificate.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the user who revoked the certificate.
     */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Generate a unique certificate number.
     *
     * @return string
     */
    public static function generateCertificateNumber(): string
    {
        do {
            $number = 'CERT-' . strtoupper(Str::random(4)) . '-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('certificate_number', $number)->exists());

        return $number;
    }

    /**
     * Generate a unique verification hash.
     *
     * @return string
     */
    public static function generateVerificationHash(): string
    {
        do {
            $hash = Str::random(32);
        } while (self::where('verification_hash', $hash)->exists());

        return $hash;
    }

    /**
     * Check if certificate is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if certificate is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if certificate is revoked.
     *
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    /**
     * Get the public verification URL.
     *
     * @return string
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('certificates.verify', $this->verification_hash);
    }

    /**
     * Get the download URL for PDF.
     *
     * @return string|null
     */
    public function getPdfDownloadUrlAttribute(): ?string
    {
        if (!$this->pdf_path || !$this->isApproved()) {
            return null;
        }

        return route('certificates.download', ['certificate' => $this->id, 'format' => 'pdf']);
    }

    /**
     * Get the download URL for PNG.
     *
     * @return string|null
     */
    public function getPngDownloadUrlAttribute(): ?string
    {
        if (!$this->png_path || !$this->isApproved()) {
            return null;
        }

        return route('certificates.download', ['certificate' => $this->id, 'format' => 'png']);
    }
}
