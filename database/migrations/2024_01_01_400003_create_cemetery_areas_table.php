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
        Schema::create('cemetery_areas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('cemetery_id')->constrained()->cascadeOnDelete();

            // Area info
            $table->string('name')->comment('Es. Sezione A, Colombario Nord, Cappella Privata');
            $table->text('description')->nullable();

            // Area type
            $table->enum('area_type', [
                'ground',    // tomba di terra
                'wall',      // colombario (loculo a muro)
                'chapel',    // cappella privata
                'ossuary',   // ossario
                'other'
            ])->default('ground');

            // Capacity
            $table->integer('total_graves')->default(0);

            // Position in cemetery
            $table->integer('floor_level')->default(0)->comment('Piano: 0=terra, 1=primo, -1=sotterraneo');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('cemetery_id');
            $table->index(['cemetery_id', 'area_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cemetery_areas');
    }
};
