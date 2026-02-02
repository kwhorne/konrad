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
        Schema::create('bank_transaction_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_transaction_id')->constrained()->cascadeOnDelete();
            $table->morphs('matchable');
            $table->enum('match_type', ['exact', 'fuzzy', 'manual']);
            $table->decimal('match_confidence', 3, 2);
            $table->foreignId('matched_by')->nullable()->constrained('users');
            $table->timestamp('matched_at');
            $table->boolean('is_confirmed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'bank_transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transaction_matches');
    }
};
