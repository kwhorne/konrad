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
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->integer('year');
            $table->integer('month');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('utbetalingsdato');

            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'reported'])->default('draft');

            // Totaler
            $table->decimal('total_bruttolonn', 14, 2)->default(0);
            $table->decimal('total_forskuddstrekk', 14, 2)->default(0);
            $table->decimal('total_nettolonn', 14, 2)->default(0);
            $table->decimal('total_feriepenger_grunnlag', 14, 2)->default(0);
            $table->decimal('total_arbeidsgiveravgift', 14, 2)->default(0);
            $table->decimal('total_otp', 14, 2)->default(0);

            // Arbeidsgiveravgift-sone
            $table->string('aga_sone', 10)->default('1');
            $table->decimal('aga_sats', 5, 2)->default(14.1);

            // Godkjenning
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
