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
            // Personal contact info
            $table->string('personal_email')->nullable()->after('personnummer');
            $table->string('phone')->nullable()->after('personal_email');

            // Address
            $table->string('address_street')->nullable()->after('phone');
            $table->string('address_postal_code', 10)->nullable()->after('address_street');
            $table->string('address_city')->nullable()->after('address_postal_code');
            $table->string('address_country', 2)->nullable()->default('NO')->after('address_city');

            // Birth date (for age-based calculations)
            $table->date('birth_date')->nullable()->after('address_country');

            // Emergency contact / next of kin
            $table->string('emergency_contact_name')->nullable()->after('birth_date');
            $table->string('emergency_contact_relation')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_relation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll_settings', function (Blueprint $table) {
            $table->dropColumn([
                'personal_email',
                'phone',
                'address_street',
                'address_postal_code',
                'address_city',
                'address_country',
                'birth_date',
                'emergency_contact_name',
                'emergency_contact_relation',
                'emergency_contact_phone',
            ]);
        });
    }
};
