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
        Schema::create('share_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique(); // AKS-2025-0001
            $table->date('transaction_date');
            $table->string('transaction_type'); // issue, transfer, redemption, split, merger, bonus
            $table->foreignId('share_class_id')->constrained();
            $table->foreignId('from_shareholder_id')->nullable()->constrained('shareholders');
            $table->foreignId('to_shareholder_id')->nullable()->constrained('shareholders');
            $table->integer('number_of_shares');
            $table->decimal('price_per_share', 15, 4)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('currency')->default('NOK');
            $table->text('description')->nullable();
            $table->string('document_reference')->nullable(); // Reference to supporting document
            $table->json('metadata')->nullable(); // Additional transaction data
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('transaction_type');
            $table->index('share_class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_transactions');
    }
};
