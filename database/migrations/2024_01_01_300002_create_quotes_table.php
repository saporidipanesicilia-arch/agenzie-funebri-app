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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('funeral_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            // Quote metadata
            $table->string('quote_number')->comment('Numero preventivo progressivo per agenzia');
            $table->enum('status', [
                'draft',      // bozza
                'sent',       // inviato alla famiglia
                'accepted',   // accettato
                'rejected',   // rifiutato
                'expired'     // scaduto
            ])->default('draft');

            // Validity
            $table->date('valid_until')->nullable()->comment('Data scadenza preventivo');

            // Discount
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('Sconto % sul totale');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Sconto fisso in €');

            // Tracking
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Timestamps
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('rejected_at')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['agency_id', 'quote_number']);
            $table->index(['funeral_id', 'status']);
            $table->index(['agency_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
