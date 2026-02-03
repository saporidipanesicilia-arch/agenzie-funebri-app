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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('funeral_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();

            // Upload tracking
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // File info
            $table->string('file_path')->comment('Storage path');
            $table->string('file_name')->comment('Original filename');
            $table->integer('file_size_kb');
            $table->string('mime_type')->nullable();

            // Status workflow
            $table->enum('status', [
                'pending',     // non ancora caricato (placeholder)
                'submitted',   // caricato, in attesa review
                'approved',    // approvato
                'rejected',    // rifiutato
                'expired'      // scaduto
            ])->default('pending');

            $table->text('rejection_reason')->nullable();

            // Versioning
            $table->integer('version')->default(1);
            $table->foreignId('replaces_document_id')->nullable()->constrained('documents')->nullOnDelete()->comment('Previous version');

            // Scadenza
            $table->date('expires_at')->nullable();

            // Timestamps
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();

            // Note
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['funeral_id', 'status']);
            $table->index(['funeral_id', 'document_type_id']);
            $table->index('uploaded_by_user_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
