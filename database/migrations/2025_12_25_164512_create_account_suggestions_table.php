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
        Schema::create('account_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('keyword', 100);
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->unsignedInteger('usage_count')->default(1);
            $table->timestamps();

            // Unik kombinasjon
            $table->unique(['contact_id', 'keyword', 'account_id']);

            // Indeks for søk
            $table->index('keyword');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_suggestions');
    }
};
