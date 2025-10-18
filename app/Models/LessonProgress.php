<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'watched_percentage',
        'last_position_seconds',
        'last_watched_at',
    ];

    protected $casts = [
        'last_watched_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('watched_percentage', '>=', 90);
    }

    public function scopeInProgress($query)
    {
        return $query->where('watched_percentage', '>', 0)
                    ->where('watched_percentage', '<', 90);
    }

    // Helper methods
    public function isCompleted()
    {
        return $this->watched_percentage >= 90;
    }

    public function isInProgress()
    {
        return $this->watched_percentage > 0 && $this->watched_percentage < 90;
    }

    public function getFormattedPositionAttribute()
    {
        $hours = floor($this->last_position_seconds / 3600);
        $minutes = floor(($this->last_position_seconds % 3600) / 60);
        $seconds = $this->last_position_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}