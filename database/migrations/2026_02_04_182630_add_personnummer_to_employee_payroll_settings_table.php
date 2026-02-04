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
        Schema::table('employee_payroll_settings', function (Blueprint $table) {
            $table->string('personnummer', 11)->nullable()->after('ansattnummer');
            $table->json('skattekort_data')->nullable()->after('skattekort_hentet_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll_settings', function (Blueprint $table) {
            $table->dropColumn(['personnummer', 'skattekort_data']);
        });
    }
};
