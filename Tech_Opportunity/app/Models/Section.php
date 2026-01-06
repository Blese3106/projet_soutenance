<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mentorship_relation_id',
        'title',
        'description',
        'type',
        'scheduled_at',
        'duration_minutes',
        'link',
        'status',
        'agenda',
        'notes',
        'feedback',
        'rating',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_minutes' => 'integer',
        'rating' => 'integer',
    ];

    // Relations
    public function mentorshipRelation()
    {
        return $this->belongsTo(MentorshipRelation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'session_participants')
            ->withPivot(['attendance_status', 'notes'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at');
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now())
            ->orWhere('status', 'completed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    // Méthodes helper
    public function isUpcoming(): bool
    {
        return $this->scheduled_at->isFuture() && $this->status === 'scheduled';
    }

    public function isPast(): bool
    {
        return $this->scheduled_at->isPast() || $this->status === 'completed';
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function getEndTime()
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes);
    }
}
