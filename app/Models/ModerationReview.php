<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'state',
        'reviewer_id',
        'submitted_by',
        'notes',
    ];

    // Relationships
    public function subject()
    {
        return $this->morphTo();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('state', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('state', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('state', 'rejected');
    }

    public function scopeDraft($query)
    {
        return $query->where('state', 'draft');
    }

    // Helper methods
    public function isPending()
    {
        return $this->state === 'pending';
    }

    public function isApproved()
    {
        return $this->state === 'approved';
    }

    public function isRejected()
    {
        return $this->state === 'rejected';
    }

    public function isDraft()
    {
        return $this->state === 'draft';
    }
}