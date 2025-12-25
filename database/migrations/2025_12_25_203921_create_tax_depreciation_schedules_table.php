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
        Schema::create('tax_depreciation_schedules', function (Blueprint $table) {
            $table->id();
            $table->year('fiscal_year');
            $table->string('depreciation_group'); // a, b, c, d, e, f, g, h, i, j
            $table->string('group_name'); // Kontormaskiner, Biler, Maskiner, etc.
            $table->decimal('depreciation_rate', 5, 2); // Avskrivningssats %
            $table->decimal('opening_balance', 15, 2)->default(0); // IB saldo
            $table->decimal('additions', 15, 2)->default(0); // Tilgang
            $table->decimal('disposals', 15, 2)->default(0); // Avgang (salgssum)
            $table->decimal('basis_for_depreciation', 15, 2)->default(0); // Avskrivningsgrunnlag
            $table->decimal('depreciation_amount', 15, 2)->default(0); // Årets avskrivning
            $table->decimal('closing_balance', 15, 2)->default(0); // UB saldo
            $table->decimal('gain_loss_account', 15, 2)->default(0); // Gevinst-/tapskonto
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['fiscal_year', 'depreciation_group']);
            $table->index('fiscal_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_depreciation_schedules');
    }
};
