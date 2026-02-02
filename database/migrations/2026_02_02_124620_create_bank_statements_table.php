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
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number', 20)->unique();
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('bank_name')->nullable();
            $table->string('account_number', 20);
            $table->foreignId('bank_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('opening_balance', 12, 2)->nullable();
            $table->decimal('closing_balance', 12, 2)->nullable();
            $table->enum('status', ['pending', 'matching', 'matched', 'reconciled', 'finalized'])->default('pending');
            $table->integer('transaction_count')->default(0);
            $table->integer('matched_count')->default(0);
            $table->integer('unmatched_count')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('finalized_by')->nullable()->constrained('users');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['from_date', 'to_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_statements');
    }
};
