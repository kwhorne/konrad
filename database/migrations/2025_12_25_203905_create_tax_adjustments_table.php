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
        Schema::create('tax_adjustments', function (Blueprint $table) {
            $table->id();
            $table->year('fiscal_year');
            $table->string('adjustment_type'); // permanent, temporary_deductible, temporary_taxable
            $table->string('category'); // entertainment, fines, unrealized_gains, depreciation_difference, etc.
            $table->string('description');
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('accounting_amount', 15, 2)->default(0); // Regnskapsmessig beløp
            $table->decimal('tax_amount', 15, 2)->default(0); // Skattemessig beløp
            $table->decimal('difference', 15, 2)->default(0); // Forskjell
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('fiscal_year');
            $table->index('adjustment_type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_adjustments');
    }
};
