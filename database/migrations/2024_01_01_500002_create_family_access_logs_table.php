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
        Schema::create('family_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_access_token_id')->constrained()->cascadeOnDelete();

            // Access details
            $table->dateTime('accessed_at');
            $table->string('ip_address', 45)->nullable()->comment('IPv4 or IPv6');
            $table->text('user_agent')->nullable();

            // What was accessed
            $table->enum('accessed_resource', [
                'funeral',     // funeral details
                'timeline',    // timeline view
                'documents',   // documents list
                'document',    // specific document
                'quote',       // quote/invoice
                'cemetery'     // cemetery info
            ]);

            // Action performed
            $table->enum('action', [
                'view',        // visualizzato
                'download'     // scaricato
            ])->default('view');

            // Resource ID (if applicable)
            $table->unsignedBigInteger('resource_id')->nullable()->comment('ID del documento/risorsa specifica');

            // Details
            $table->text('details')->nullable()->comment('Additional context (JSON)');

            $table->timestamps();

            // Indexes
            $table->index('family_access_token_id');
            $table->index('accessed_at');
            $table->index(['accessed_resource', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_access_logs');
    }
};
