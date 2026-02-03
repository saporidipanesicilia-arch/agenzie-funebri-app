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
        Schema::create('burials', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('grave_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deceased_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('funeral_id')->nullable()->constrained()->nullOnDelete();

            // Burial info
            $table->date('burial_date');
            $table->enum('burial_type', [
                'inhumation',     // inumazione (terra)
                'entombment',     // tumulazione (loculo)
                'cremation_urn'   // urna ceneri
            ])->default('entombment');

            // Details (if not linked to deceased)
            $table->string('deceased_name')->nullable()->comment('Nome defunto (se non collegato a deceased_id)');
            $table->date('death_date')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('grave_id');
            $table->index('deceased_id');
            $table->index('burial_date');
            $table->index('deceased_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('burials');
    }
};
