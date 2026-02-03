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
        Schema::create('relatives', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('funeral_id')->constrained()->cascadeOnDelete();

            // Anagrafica
            $table->string('name');
            $table->string('relation_type')->nullable()->comment('es. coniuge, figlio, padre, madre');

            // Contatti
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Referente principale
            $table->boolean('is_primary_contact')->default(false);

            // Note
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('funeral_id');
            $table->index(['funeral_id', 'is_primary_contact']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relatives');
    }
};
