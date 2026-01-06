<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete(); // L'apprenant
            $table->enum('status', [
                'pending',           
                'test_in_progress',  
                'test_completed',  
                'test_passed',  
                'test_failed', 
                'meet_scheduled',
                'under_review', 
                'accepted',  
                'rejected',  
                'withdrawn'  
            ])->default('pending');
            
            $table->text('cover_letter')->nullable(); // Lettre de motivation
            $table->string('resume_path')->nullable(); // CV (fichier)
            $table->string('portfolio_link')->nullable(); // Lien portfolio

            $table->dateTime('test_started_at')->nullable();
            $table->dateTime('test_completed_at')->nullable();
            $table->integer('test_score')->nullable(); 
            $table->integer('test_total_points')->nullable(); 
            $table->json('test_answers')->nullable();
            
            // Informations sur le meet
            $table->dateTime('meet_scheduled_at')->nullable();
            $table->text('meet_notes')->nullable();
            
            // Feedback final
            $table->text('rejection_reason')->nullable();
            $table->text('acceptance_note')->nullable(); 
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['offer_id', 'applicant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
