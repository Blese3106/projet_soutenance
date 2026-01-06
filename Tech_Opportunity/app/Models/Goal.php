<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mentorship_relation_id',
        'title',
        'description',
        'priority',
        'status',
        'target_date',
        'completed_at',
        'progress',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'date',
        'progress' => 'integer',
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

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // Méthodes helper
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->target_date && 
               $this->target_date->isPast() && 
               !$this->isCompleted();
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress' => 100,
        ]);
    }
}
