<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'posted_by',
        'title',
        'description',
        'type',
        'contract_type',
        'company_name',
        'location',
        'work_mode',
        'salary_range',
        'required_skills',
        'responsibilities',
        'benefits',
        'application_deadline',
        'status',
        'validation_type',
        'meet_link',
        'has_test',
        'test_duration_minutes',
        'test_passing_score',
        'views_count',
        'applications_count',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'has_test' => 'boolean',
        'test_duration_minutes' => 'integer',
        'test_passing_score' => 'integer',
        'views_count' => 'integer',
        'applications_count' => 'integer',
    ];

    // Relations
    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class)->orderBy('order');
    }

    public function views()
    {
        return $this->hasMany(OfferView::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('application_deadline')
                  ->orWhere('application_deadline', '>=', now());
            });
    }

    public function scopeByPoster($query, $userId)
    {
        return $query->where('posted_by', $userId);
    }

    // Méthodes helper
    public function isActive(): bool
    {
        return $this->status === 'published' && 
               (!$this->application_deadline || $this->application_deadline->isFuture());
    }

    public function hasDeadlinePassed(): bool
    {
        return $this->application_deadline && $this->application_deadline->isPast();
    }

    public function requiresTest(): bool
    {
        return in_array($this->validation_type, ['test', 'test_then_meet']);
    }

    public function requiresMeet(): bool
    {
        return in_array($this->validation_type, ['direct_meet', 'test_then_meet']);
    }

    public function canApply(): bool
    {
        return $this->isActive() && $this->status === 'published';
    }

    public function hasApplied($userId): bool
    {
        return $this->applications()->where('applicant_id', $userId)->exists();
    }

    public function getAcceptanceRate(): float
    {
        $total = $this->applications()->count();
        if ($total === 0) return 0;
        
        $accepted = $this->applications()->where('status', 'accepted')->count();
        return ($accepted / $total) * 100;
    }

    public function getPendingApplicationsCount(): int
    {
        return $this->applications()
            ->whereIn('status', ['pending', 'test_in_progress', 'test_completed', 'under_review'])
            ->count();
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementApplications()
    {
        $this->increment('applications_count');
    }
}
