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
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            // Definizione tipo documento
            $table->string('name')->comment('Es. Certificato di morte, Permesso sepoltura');
            $table->text('description')->nullable();

            // Configurazione
            $table->boolean('is_required')->default(false);
            $table->json('required_for_service_types')->nullable()->comment('Array: burial, cremation, entombment');

            // Validazione file
            $table->integer('max_file_size_mb')->default(10);
            $table->json('allowed_extensions')->default('["pdf","jpg","jpeg","png"]');

            // Scadenza
            $table->integer('expiry_days')->nullable()->comment('Giorni validità documento');

            // Template fields per OCR futuro
            $table->json('template_fields')->nullable()->comment('Campi da estrarre con OCR');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['agency_id', 'is_required']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
