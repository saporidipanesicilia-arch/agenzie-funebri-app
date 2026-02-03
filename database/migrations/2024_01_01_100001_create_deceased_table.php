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
        Schema::create('deceased', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('funeral_id')->nullable()->constrained()->cascadeOnDelete();

            // Anagrafica
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->date('death_date');
            $table->string('place_of_birth')->nullable();
            $table->string('place_of_death')->nullable();

            // Documenti
            $table->string('tax_code')->nullable()->comment('Codice fiscale');

            // Note
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes - non serve agency_id perché è legato a funeral
            $table->index('funeral_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deceased');
    }
};
