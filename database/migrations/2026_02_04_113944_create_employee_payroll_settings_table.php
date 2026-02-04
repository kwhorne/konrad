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
        Schema::create('employee_payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Ansettelsesinfo
            $table->string('ansattnummer', 20)->nullable();
            $table->date('ansatt_fra')->nullable();
            $table->date('ansatt_til')->nullable();
            $table->decimal('stillingsprosent', 5, 2)->default(100.00);
            $table->string('stilling')->nullable();

            // Lonnstype
            $table->enum('lonn_type', ['fast', 'time'])->default('fast');
            $table->decimal('maanedslonn', 12, 2)->nullable();
            $table->decimal('timelonn', 10, 2)->nullable();
            $table->decimal('aarslonn', 14, 2)->nullable();

            // Skattekort
            $table->enum('skatt_type', ['tabelltrekk', 'prosenttrekk', 'kildeskatt', 'frikort'])->default('tabelltrekk');
            $table->string('skattetabell', 10)->nullable();
            $table->decimal('skatteprosent', 5, 2)->nullable();
            $table->decimal('frikort_belop', 12, 2)->nullable();
            $table->decimal('frikort_brukt', 12, 2)->default(0);
            $table->date('skattekort_gyldig_fra')->nullable();
            $table->date('skattekort_gyldig_til')->nullable();
            $table->timestamp('skattekort_hentet_at')->nullable();

            // Feriepenger
            $table->decimal('feriepenger_prosent', 5, 2)->default(10.2);
            $table->boolean('ferie_5_uker')->default(false);
            $table->boolean('over_60')->default(false);

            // OTP
            $table->boolean('otp_enabled')->default(true);
            $table->decimal('otp_prosent', 5, 2)->default(2.0);

            // Bankinfo
            $table->string('kontonummer', 11)->nullable();

            // Aa-registeret
            $table->string('aa_arbeidsforhold_id')->nullable();
            $table->timestamp('aa_synced_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
            $table->unique(['company_id', 'ansattnummer']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_settings');
    }
};
