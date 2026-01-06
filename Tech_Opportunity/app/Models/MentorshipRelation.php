<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MentorshipRelation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mentor_id',
        'mentee_id',
        'status',
        'start_date',
        'end_date',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relations
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function objectives()
    {
        return $this->hasMany(Goal::class);
    }

    public function sessions()
    {
        return $this->hasMany(Section::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Méthodes helper
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getObjectivesCompletionRate(): float
    {
        $total = $this->objectives()->count();
        if ($total === 0) return 0;
        
        $completed = $this->objectives()->where('status', 'completed')->count();
        return ($completed / $total) * 100;
    }

    public function getUpcomingSessions()
    {
        return $this->sessions()
            ->where('scheduled_at', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->get();
    }
}
