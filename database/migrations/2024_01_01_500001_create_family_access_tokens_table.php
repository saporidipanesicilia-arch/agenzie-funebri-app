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
        Schema::create('family_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('funeral_id')->constrained()->cascadeOnDelete();

            // Token (hashed for security)
            $table->string('token', 64)->unique()->comment('SHA-256 hash del token');
            $table->text('token_plain')->comment('Token in chiaro (encrypted)');

            // Access control
            $table->enum('access_level', [
                'full',            // tutto
                'limited',         // solo info base
                'documents_only',  // solo documenti
                'cemetery_only'    // solo cimitero
            ])->default('limited');

            // Who is granted access
            $table->string('granted_to_name')->comment('Nome del familiare');
            $table->string('granted_to_email')->nullable();
            $table->string('granted_to_phone')->nullable();

            // Expiration
            $table->dateTime('expires_at')->nullable()->comment('NULL = non scade mai');

            // Usage limits
            $table->integer('max_uses')->nullable()->comment('NULL = illimitato');
            $table->integer('current_uses')->default(0);

            // Status
            $table->boolean('is_active')->default(true);
            $table->dateTime('revoked_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // QR code
            $table->string('qr_code_path')->nullable()->comment('Path to QR code PNG');

            // Audit
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('funeral_id');
            $table->index('token');
            $table->index(['funeral_id', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_access_tokens');
    }
};
