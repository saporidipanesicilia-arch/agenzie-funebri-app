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
        Schema::create('cemetery_maps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('cemetery_id')->constrained()->cascadeOnDelete();

            // Map info
            $table->string('name')->comment('Es. Sezione A - Piano terra, Colombario Nord');
            $table->text('description')->nullable();

            // File
            $table->string('file_path');
            $table->string('file_name');
            $table->enum('file_type', ['pdf', 'jpg', 'jpeg', 'png'])->default('pdf');
            $table->integer('file_size_kb');

            // Ordering
            $table->integer('order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('cemetery_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cemetery_maps');
    }
};
