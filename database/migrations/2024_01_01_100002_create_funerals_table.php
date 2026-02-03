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
        Schema::create('funerals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            // Tipo di servizio
            $table->enum('service_type', [
                'burial',      // inumazione
                'entombment',  // tumulazione
                'cremation'    // cremazione
            ]);

            // Stato pratica
            $table->enum('status', [
                'draft',       // bozza
                'active',      // in corso
                'completed',   // completato
                'cancelled'    // annullato
            ])->default('draft');

            // Date cerimonia
            $table->dateTime('ceremony_date')->nullable();
            $table->string('ceremony_location')->nullable();

            // Timeline generale
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Note
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['agency_id', 'status', 'created_at']);
            $table->index(['branch_id', 'status']);
            $table->index('ceremony_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funerals');
    }
};
