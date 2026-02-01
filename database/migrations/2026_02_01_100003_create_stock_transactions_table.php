<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop empty legacy table if it exists
        Schema::dropIfExists('stock_movements');

        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_number', 50)->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_location_id')->constrained()->restrictOnDelete();
            $table->foreignId('to_stock_location_id')->nullable()->constrained('stock_locations')->restrictOnDelete();
            $table->enum('transaction_type', [
                'receipt',
                'issue',
                'transfer_out',
                'transfer_in',
                'adjustment_in',
                'adjustment_out',
                'count_adjustment',
            ]);
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->decimal('quantity_before', 12, 2)->default(0);
            $table->decimal('quantity_after', 12, 2)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->index('company_id');
            $table->index(['company_id', 'transaction_date']);
            $table->index(['product_id', 'stock_location_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('transaction_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
