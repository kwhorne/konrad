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
        Schema::create('company_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('enabled_by', 50)->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_subscription_status', 50)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'module_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_modules');
    }
};
