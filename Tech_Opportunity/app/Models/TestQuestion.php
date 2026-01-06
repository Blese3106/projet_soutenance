<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'question',
        'type',
        'options',
        'correct_answers',
        'expected_answer',
        'points',
        'order',
        'explanation',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'points' => 'integer',
        'order' => 'integer',
    ];

    // Relations
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    // Méthodes helper
    public function checkAnswer($userAnswer): bool
    {
        switch ($this->type) {
            case 'mcq':
                return $userAnswer == $this->correct_answers[0];
                
            case 'multiple_choice':
                $userAnswers = is_array($userAnswer) ? $userAnswer : [$userAnswer];
                sort($userAnswers);
                $correctAnswers = $this->correct_answers;
                sort($correctAnswers);
                return $userAnswers === $correctAnswers;
                
            case 'text':
                return strtolower(trim($userAnswer)) === strtolower(trim($this->expected_answer));
                
            case 'code':
                // Pour le code, l'évaluation peut être manuelle ou via des tests automatisés
                return false; // À évaluer manuellement
                
            default:
                return false;
        }
    }
}