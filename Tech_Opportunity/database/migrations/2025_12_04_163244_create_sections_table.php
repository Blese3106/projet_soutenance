<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentorship_relation_id')->constrained()->onDelete('cascade');
            // $table->foreignId('mentor_or_coach_id')->constrained('users')->onDelete('cascade');
            // $table->foreignId('mentee_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('link')->nullable();
            $table->enum('type', ['mentorat','coaching']);
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('agenda')->nullable(); // Ordre du jour
            $table->text('notes')->nullable(); // Notes après la session
            $table->text('feedback')->nullable(); // Retour du mentoré
            $table->integer('rating')->nullable(); // Note de 1 à 5
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
