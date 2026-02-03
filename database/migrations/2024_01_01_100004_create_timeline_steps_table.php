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
        Schema::create('timeline_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            // Definizione step
            $table->string('name')->comment('es. Ritiro salma, Vestizione, Cerimonia');
            $table->text('description')->nullable();
            $table->integer('order')->default(0)->comment('Ordine di esecuzione');

            // Configurazione
            $table->boolean('is_required')->default(true);
            $table->integer('estimated_duration_hours')->nullable();

            // Documenti richiesti (JSON)
            $table->json('required_documents')->nullable()->comment('Array di tipi documento richiesti');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['agency_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_steps');
    }
};
