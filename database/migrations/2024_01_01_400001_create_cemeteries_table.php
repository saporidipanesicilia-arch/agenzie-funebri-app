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
        Schema::create('cemeteries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            // Cemetery info
            $table->string('name')->comment('Es. Cimitero Monumentale Milano');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Capacity
            $table->integer('total_graves')->default(0)->comment('Capacità totale');

            // Status
            $table->boolean('is_active')->default(true);

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('agency_id');
            $table->index(['agency_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cemeteries');
    }
};
