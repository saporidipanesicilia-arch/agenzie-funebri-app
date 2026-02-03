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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('Nome sede (es. Sede di Milano)');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_main')->default(false)->comment('Sede principale?');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'created_at']);
            $table->index(['agency_id', 'is_main']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
