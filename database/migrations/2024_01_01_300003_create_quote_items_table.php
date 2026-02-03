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
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();

            // Item classification
            $table->enum('item_type', [
                'coffin',      // cofano
                'flowers',     // fiori
                'transport',   // trasporto
                'service',     // servizio (vestizione, tanatoprassi, etc)
                'ceremony',    // cerimonia religiosa/laica
                'grave',       // loculo/tomba
                'documents',   // pratiche amministrative
                'other'        // altro
            ])->default('other');

            // Item description
            $table->string('description')->comment('Es. Cofano impiallacciato noce, Trasporto con carro funebre');

            // Pricing
            $table->decimal('cost_price', 10, 2)->comment('Costo unitario (€)');
            $table->decimal('selling_price', 10, 2)->comment('Prezzo vendita unitario (€)');
            $table->decimal('quantity', 8, 2)->default(1)->comment('Quantità');

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('quote_id');
            $table->index('item_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
