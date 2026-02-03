<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('funeral_timeline', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('funeral_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timeline_step_id')->constrained()->cascadeOnDelete();

            // Assignment
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Status & tracking
            $table->enum('status', [
                'pending',      // da fare
                'in_progress',  // in corso
                'completed',    // completato
                'skipped'       // saltato
            ])->default('pending');

            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            // Note specifiche per questo step in questo funerale
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['funeral_id', 'status']);
            $table->index('assigned_user_id');
            $table->index(['funeral_id', 'timeline_step_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funeral_timeline');
    }
};
