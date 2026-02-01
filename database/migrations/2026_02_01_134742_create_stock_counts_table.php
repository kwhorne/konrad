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
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('count_number', 20)->unique(); // VT-YYYY-NNNN
            $table->foreignId('stock_location_id')->constrained()->cascadeOnDelete();
            $table->date('count_date');
            $table->string('description')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed', 'posted', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('total_expected_value', 14, 2)->default(0);
            $table->decimal('total_counted_value', 14, 2)->default(0);
            $table->decimal('total_variance_value', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'count_date']);
        });

        Schema::create('stock_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_count_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('expected_quantity', 12, 2)->default(0);
            $table->decimal('counted_quantity', 12, 2)->nullable();
            $table->decimal('variance_quantity', 12, 2)->nullable();
            $table->decimal('unit_cost', 12, 4)->default(0); // Vektet gj.snitt ved telling
            $table->decimal('expected_value', 14, 2)->default(0);
            $table->decimal('counted_value', 14, 2)->nullable();
            $table->decimal('variance_value', 14, 2)->nullable();
            $table->text('variance_reason')->nullable();
            $table->boolean('is_counted')->default(false);
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['stock_count_id', 'product_id']);
            $table->index(['stock_count_id', 'is_counted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_count_lines');
        Schema::dropIfExists('stock_counts');
    }
};
