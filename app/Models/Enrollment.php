<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'paid_amount',
        'currency',
        'payment_id',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_amount');
    }

    public function scopeFree($query)
    {
        return $query->whereNull('paid_amount');
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPaid()
    {
        return !is_null($this->paid_amount);
    }

    public function isFree()
    {
        return is_null($this->paid_amount);
    }

    /**
     * Calculate the completion percentage for this enrollment
     * 
     * @return float
     */
    public function getCompletionPercentage(): float
    {
        $totalLessons = $this->course->lessons()->where('is_published', true)->count();
        
        if ($totalLessons === 0) {
            return 0;
        }

        // Get only published lesson IDs
        $publishedLessonIds = $this->course->lessons()
            ->where('is_published', true)
            ->pluck('id');

        // Get completed lessons for this user in this course (only published)
        $completedLessons = \App\Models\LessonProgress::where('user_id', $this->user_id)
            ->whereIn('lesson_id', $publishedLessonIds)
            ->where('watched_percentage', '>=', 90)
            ->count();

        return round(($completedLessons / $totalLessons) * 100, 2);
    }

    /**
     * Get formatted completion percentage
     * 
     * @return string
     */
    public function getFormattedCompletionAttribute(): string
    {
        return number_format($this->getCompletionPercentage(), 2) . '%';
    }
}