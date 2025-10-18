<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'video_url',
        'hls_manifest_url',
        'duration_seconds',
        'is_published',
        'order',
        'is_free_preview',
        'resources',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_free_preview' => 'boolean',
        'resources' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = Str::slug($lesson->title);
            }
        });
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function moderationReview()
    {
        return $this->morphOne(ModerationReview::class, 'subject');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFreePreview($query)
    {
        return $query->where('is_free_preview', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Helper methods
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getVideoUrlAttribute($value)
    {
        if ($this->hls_manifest_url) {
            return $this->hls_manifest_url;
        }

        return $value;
    }

    public function isFreePreview()
    {
        return $this->is_free_preview;
    }

    public function isPublished()
    {
        return $this->is_published;
    }
}