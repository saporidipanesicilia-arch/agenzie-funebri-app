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
        Schema::create('graves', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('cemetery_area_id')->constrained()->cascadeOnDelete();

            // Grave identification
            $table->string('grave_number')->comment('Numero tomba/loculo (es. A-123, L-45)');

            // Grave type
            $table->enum('grave_type', [
                'loculo',          // loculo (niche)
                'tomba_famiglia',  // tomba di famiglia
                'campo_comune',    // campo comune (temporary)
                'ossario',         // ossario
                'celletta',        // celletta
                'cappella'         // cappella privata
            ])->default('loculo');

            // Status
            $table->enum('status', [
                'available',    // disponibile
                'occupied',     // occupato
                'reserved',     // riservato
                'maintenance'   // in manutenzione
            ])->default('available');

            // Position (grid system)
            $table->integer('row')->nullable()->comment('Fila');
            $table->integer('column')->nullable()->comment('Colonna');

            // Capacity
            $table->integer('max_burials')->default(1)->comment('Numero massimo sepolture');
            $table->integer('current_burials')->default(0);

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('cemetery_area_id');
            $table->index(['cemetery_area_id', 'status']);
            $table->index('grave_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graves');
    }
};
