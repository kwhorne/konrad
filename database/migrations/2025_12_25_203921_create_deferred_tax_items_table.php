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
        Schema::create('deferred_tax_items', function (Blueprint $table) {
            $table->id();
            $table->year('fiscal_year');
            $table->string('item_type'); // asset, liability
            $table->string('category'); // fixed_assets, receivables, provisions, losses_carried_forward, etc.
            $table->string('description');
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('accounting_value', 15, 2)->default(0); // Regnskapsmessig verdi
            $table->decimal('tax_value', 15, 2)->default(0); // Skattemessig verdi
            $table->decimal('temporary_difference', 15, 2)->default(0); // Midlertidig forskjell
            $table->decimal('deferred_tax', 15, 2)->default(0); // Utsatt skatt (22%)
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('fiscal_year');
            $table->index('item_type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deferred_tax_items');
    }
};
