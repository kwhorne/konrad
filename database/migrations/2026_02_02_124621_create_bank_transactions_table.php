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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_statement_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->date('value_date')->nullable();
            $table->text('description');
            $table->string('reference', 100)->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('running_balance', 12, 2)->nullable();
            $table->enum('transaction_type', ['credit', 'debit']);
            $table->string('counterparty_name')->nullable();
            $table->string('counterparty_account', 20)->nullable();
            $table->json('raw_data')->nullable();
            $table->enum('match_status', ['unmatched', 'auto_matched', 'manual_matched', 'ignored'])->default('unmatched');
            $table->decimal('match_confidence', 3, 2)->nullable();
            $table->integer('sort_order');
            $table->timestamps();

            $table->index(['company_id', 'transaction_date']);
            $table->index(['bank_statement_id', 'match_status']);
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
