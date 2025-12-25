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
        Schema::create('tax_returns', function (Blueprint $table) {
            $table->id();
            $table->year('fiscal_year')->unique();
            $table->date('period_start');
            $table->date('period_end');

            // Resultatregnskap til skattemessig resultat
            $table->decimal('accounting_profit', 15, 2)->default(0); // Regnskapsmessig resultat
            $table->decimal('permanent_differences', 15, 2)->default(0); // Permanente forskjeller
            $table->decimal('temporary_differences_change', 15, 2)->default(0); // Endring midlertidige forskjeller
            $table->decimal('taxable_income', 15, 2)->default(0); // Skattepliktig inntekt

            // Skatt
            $table->decimal('tax_rate', 5, 2)->default(22.00); // Skattesats
            $table->decimal('tax_payable', 15, 2)->default(0); // Betalbar skatt
            $table->decimal('deferred_tax_change', 15, 2)->default(0); // Endring utsatt skatt
            $table->decimal('total_tax_expense', 15, 2)->default(0); // Total skattekostnad

            // Fremførbart underskudd
            $table->decimal('losses_brought_forward', 15, 2)->default(0); // Underskudd til fremføring IB
            $table->decimal('losses_used', 15, 2)->default(0); // Benyttet underskudd
            $table->decimal('losses_carried_forward', 15, 2)->default(0); // Underskudd til fremføring UB

            // Status
            $table->string('status')->default('draft'); // draft, ready, submitted
            $table->json('calculation_details')->nullable(); // Detaljert beregning
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('altinn_submission_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_returns');
    }
};
