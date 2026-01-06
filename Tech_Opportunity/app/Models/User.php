<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'bio'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mentees()
    {
        return $this->belongsToMany(User::class, 'mentorship_relations', 'mentor_id', 'mentee_id')
            ->withPivot(['status', 'start_date', 'end_date', 'description'])
            ->withTimestamps();
    }

    public function mentors()
    {
        return $this->belongsToMany(User::class, 'mentorship_relations', 'mentee_id', 'mentor_id')
            ->withPivot(['status', 'start_date', 'end_date', 'description'])
            ->withTimestamps();
    }

    public function postedOffers()
    {
        return $this->hasMany(Offer::class, 'posted_by');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'applicant_id');
    }

    public function reviewedApplications()
    {
        return $this->hasMany(Application::class, 'reviewed_by');
    }

    public function mentorshipAsMentor()
    {
        return $this->hasMany(MentorshipRelation::class, 'mentor_id');
    }

    public function mentorshipAsMentee()
    {
        return $this->hasMany(MentorshipRelation::class, 'mentee_id');
    }

    public function createdSessions()
    {
        return $this->hasMany(Section::class, 'created_by');
    }

    // Sessions où l'utilisateur participe
    public function sessions()
    {
        return $this->belongsToMany(Section::class, 'session_participants')
            ->withPivot(['attendance_status', 'notes'])
            ->withTimestamps();
    }

    // Objectifs créés par l'utilisateur
    public function createdObjectives()
    {
        return $this->hasMany(Goal::class, 'created_by');
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeMentors($query)
    {
        return $query->where('role', 'mentor');
    }

    public function scopeMentees($query)
    {
        return $query->where('role', 'mentee');
    }

    public function scopeCoaches($query)
    {
        return $query->where('role', 'coach');
    }

    // Méthodes helper
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMentor(): bool
    {
        return $this->role === 'mentor';
    }

    public function isMentee(): bool
    {
        return $this->role === 'mentee';
    }

    public function isCoach(): bool
    {
        return $this->role === 'coach';
    }

}
