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
        Schema::create('margin_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->unique()->constrained()->cascadeOnDelete();

            // Soglie margine (percentuali)
            $table->decimal('minimum_margin_percentage', 5, 2)->default(25.00)->comment('Margine minimo consigliato (%)');
            $table->decimal('warning_margin_percentage', 5, 2)->default(15.00)->comment('Soglia warning (%)');
            $table->decimal('critical_margin_percentage', 5, 2)->default(5.00)->comment('Soglia critica (%)');

            // Alert configuration
            $table->boolean('alert_enabled')->default(true);
            $table->boolean('block_negative_margin')->default(true)->comment('Blocca preventivi in perdita');
            $table->boolean('require_approval_for_low_margin')->default(true)->comment('Richiedi approvazione per margini bassi');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('margin_settings');
    }
};
