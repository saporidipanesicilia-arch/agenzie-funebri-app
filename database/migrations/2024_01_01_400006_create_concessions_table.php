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
        Schema::create('concessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('grave_id')->constrained()->cascadeOnDelete();
            $table->foreignId('burial_id')->nullable()->constrained()->nullOnDelete();

            // Concessionaire (intestatario)
            $table->string('concessionaire_name');
            $table->string('concessionaire_tax_code')->nullable();
            $table->string('concessionaire_phone')->nullable();
            $table->string('concessionaire_email')->nullable();
            $table->text('concessionaire_address')->nullable();

            // Concession dates
            $table->date('concession_date')->comment('Data concessione');
            $table->date('expiry_date')->comment('Data scadenza');
            $table->integer('duration_years')->comment('Durata in anni (20, 30, 50, 99)');

            // Renewal tracking
            $table->integer('renewal_count')->default(0);
            $table->date('last_renewal_date')->nullable();
            $table->boolean('auto_renewal')->default(false);

            // Status
            $table->enum('status', [
                'active',      // attiva
                'expiring',    // in scadenza (< 90 giorni)
                'expired',     // scaduta
                'renewed',     // rinnovata
                'terminated'   // cessata
            ])->default('active');

            // Fees
            $table->decimal('fee_paid', 10, 2)->nullable()->comment('Importo pagato');
            $table->date('fee_paid_date')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('grave_id');
            $table->index('expiry_date');
            $table->index(['status', 'expiry_date']);
            $table->index('concessionaire_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concessions');
    }
};
