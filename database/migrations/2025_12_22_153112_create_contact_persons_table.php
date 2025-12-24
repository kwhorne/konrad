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
        Schema::create('contact_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade');

            // Personal Info
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('department')->nullable();

            // Contact Info
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Social Media
            $table->string('linkedin')->nullable();

            // Additional Info
            $table->text('notes')->nullable();
            $table->date('birthday')->nullable();

            // Status
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('contact_id');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_persons');
    }
};
