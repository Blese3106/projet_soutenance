<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posted_by')->constrained('users')->onDelete('cascade');
            $table->string('title'); 
            $table->text('description'); 
            $table->enum('type', ['job', 'internship', 'freelance', 'project'])->default('job');
            $table->enum('contract_type', ['cdi', 'cdd', 'stage', 'freelance', 'other'])->nullable();
            $table->string('company_name')->nullable(); 
            $table->string('location')->nullable(); 
            $table->enum('work_mode', ['remote', 'onsite', 'hybrid'])->default('onsite');
            $table->string('salary_range')->nullable(); 
            $table->text('required_skills')->nullable();
            $table->text('responsibilities')->nullable();
            $table->text('benefits')->nullable();
            $table->date('application_deadline')->nullable();
            $table->enum('status', ['draft', 'published', 'closed', 'cancelled'])->default('published');
          
            $table->enum('validation_type', ['direct_meet', 'test', 'test_then_meet'])->default('direct_meet');
            
            $table->string('meet_link')->nullable();
            $table->boolean('has_test')->default(false);
            $table->integer('test_duration_minutes')->nullable(); 
            $table->integer('test_passing_score')->nullable();
            
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0); 
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
