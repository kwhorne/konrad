<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('altinn_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('certificate')->nullable(); // Encrypted PEM content
            $table->text('private_key')->nullable(); // Encrypted private key
            $table->string('passphrase')->nullable(); // Encrypted passphrase
            $table->string('file_path')->nullable(); // Path to certificate file (alternative to storing in DB)
            $table->string('serial_number')->nullable();
            $table->string('issuer')->nullable();
            $table->string('subject')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('altinn_certificates');
    }
};
