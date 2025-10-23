<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'level',
        'price',
        'currency',
        'is_published',
        'published_at',
        'thumbnail_url',
        'created_by',
        'free_lesson_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($course) {
            if (empty($course->slug)) {
                $baseSlug = Str::slug($course->title);
                $slug = $baseSlug;
                $counter = 1;
                
                // Check including soft deleted records to ensure uniqueness
                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                
                $course->slug = $slug;
            }
        });
        
        // When restoring a soft-deleted course, ensure slug is still unique
        static::restoring(function ($course) {
            // Get the base slug (remove any numeric suffix)
            $baseSlug = preg_replace('/-\d+$/', '', $course->slug);
            $slug = $course->slug;
            $counter = 1;
            
            // Check if current slug is taken by another non-deleted course
            if (static::where('slug', $slug)->where('id', '!=', $course->id)->exists()) {
                // Find next available slug
                $slug = $baseSlug;
                while (static::where('slug', $slug)->where('id', '!=', $course->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $course->slug = $slug;
            }
        });
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function publishedLessons()
    {
        return $this->hasMany(Lesson::class)->where('is_published', true)->orderBy('order');
    }

    public function freePreviewLessons()
    {
        return $this->hasMany(Lesson::class)
                    ->where('is_published', true)
                    ->where('is_free_preview', true)
                    ->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot(['status', 'paid_amount', 'currency', 'payment_id'])
                    ->withTimestamps();
    }

    public function courseCompletions()
    {
        return $this->hasMany(CourseCompletion::class);
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

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeFree($query)
    {
        return $query->whereNull('price');
    }

    public function scopePaid($query)
    {
        return $query->whereNotNull('price');
    }

    // Helper methods
    public function isFree()
    {
        return is_null($this->price);
    }

    public function isPaid()
    {
        return !is_null($this->price);
    }

    public function getFormattedPriceAttribute()
    {
        if ($this->isFree()) {
            return 'Free';
        }
        
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    public function getTotalLessonsCountAttribute()
    {
        return $this->lessons()->count();
    }

    public function getPublishedLessonsCountAttribute()
    {
        return $this->publishedLessons()->count();
    }

    public function getFreePreviewLessonsCountAttribute()
    {
        return $this->freePreviewLessons()->count();
    }

    public function getEnrollmentsCountAttribute()
    {
        return $this->enrollments()->where('status', 'active')->count();
    }
}