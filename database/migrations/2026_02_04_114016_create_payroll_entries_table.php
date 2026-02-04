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
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Timer (fra timeregistrering)
            $table->decimal('timer_ordinaer', 8, 2)->default(0);
            $table->decimal('timer_overtid_50', 8, 2)->default(0);
            $table->decimal('timer_overtid_100', 8, 2)->default(0);

            // Inntekter
            $table->decimal('grunnlonn', 12, 2)->default(0);
            $table->decimal('overtid_belop', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('tillegg', 12, 2)->default(0);
            $table->decimal('bruttolonn', 12, 2)->default(0);

            // Trekk
            $table->decimal('forskuddstrekk', 12, 2)->default(0);
            $table->decimal('fagforening', 10, 2)->default(0);
            $table->decimal('andre_trekk', 10, 2)->default(0);
            $table->decimal('nettolonn', 12, 2)->default(0);

            // Arbeidsgiverkostnader
            $table->decimal('feriepenger_grunnlag', 12, 2)->default(0);
            $table->decimal('feriepenger_avsetning', 12, 2)->default(0);
            $table->decimal('arbeidsgiveravgift', 12, 2)->default(0);
            $table->decimal('otp_belop', 10, 2)->default(0);

            // Skatteinfo brukt
            $table->string('skatt_type_brukt', 20)->nullable();
            $table->decimal('skatteprosent_brukt', 5, 2)->nullable();

            // A-melding data
            $table->json('a_melding_data')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
