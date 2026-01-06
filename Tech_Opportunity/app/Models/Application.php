<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'offer_id',
        'applicant_id',
        'status',
        'cover_letter',
        'resume_path',
        'portfolio_link',
        'test_started_at',
        'test_completed_at',
        'test_score',
        'test_total_points',
        'test_answers',
        'meet_scheduled_at',
        'meet_notes',
        'rejection_reason',
        'acceptance_note',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'test_started_at' => 'datetime',
        'test_completed_at' => 'datetime',
        'meet_scheduled_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'test_score' => 'integer',
        'test_total_points' => 'integer',
        'test_answers' => 'array',
    ];

    // Relations
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    // Méthodes helper
    public function canStartTest(): bool
    {
        return $this->status === 'pending' && 
               $this->offer->requiresTest() && 
               !$this->test_started_at;
    }

    public function isTestInProgress(): bool
    {
        return $this->status === 'test_in_progress';
    }

    public function hasPassedTest(): bool
    {
        return $this->status === 'test_passed';
    }

    public function hasFailedTest(): bool
    {
        return $this->status === 'test_failed';
    }

    public function getTestPercentage(): ?float
    {
        if (!$this->test_score || !$this->test_total_points) {
            return null;
        }
        return ($this->test_score / $this->test_total_points) * 100;
    }

    public function hasTestExpired(): bool
    {
        if (!$this->test_started_at || !$this->offer->test_duration_minutes) {
            return false;
        }
        
        $expiryTime = $this->test_started_at->addMinutes($this->offer->test_duration_minutes);
        return now()->isAfter($expiryTime);
    }

    public function getRemainingTestTime(): int
    {
        if (!$this->test_started_at || !$this->offer->test_duration_minutes) {
            return 0;
        }
        
        $expiryTime = $this->test_started_at->addMinutes($this->offer->test_duration_minutes);
        $remaining = now()->diffInMinutes($expiryTime, false);
        
        return max(0, $remaining);
    }

    public function evaluateTest(array $userAnswers)
    {
        $totalScore = 0;
        $maxScore = 0;
        
        foreach ($this->offer->testQuestions as $question) {
            $maxScore += $question->points;
            
            if (isset($userAnswers[$question->id])) {
                if ($question->checkAnswer($userAnswers[$question->id])) {
                    $totalScore += $question->points;
                }
            }
        }
        
        $this->update([
            'test_score' => $totalScore,
            'test_total_points' => $maxScore,
            'test_answers' => $userAnswers,
            'test_completed_at' => now(),
            'status' => $this->determineStatusAfterTest($totalScore, $maxScore),
        ]);
        
        return $this->test_score;
    }

    private function determineStatusAfterTest($score, $maxScore): string
    {
        $percentage = ($score / $maxScore) * 100;
        $passingScore = $this->offer->test_passing_score ?? 60;
        
        if ($percentage >= $passingScore) {
            return $this->offer->requiresMeet() ? 'test_passed' : 'under_review';
        }
        
        return 'test_failed';
    }

    public function accept($note = null)
    {
        $this->update([
            'status' => 'accepted',
            'acceptance_note' => $note,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);
    }

    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);
    }
}