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
}