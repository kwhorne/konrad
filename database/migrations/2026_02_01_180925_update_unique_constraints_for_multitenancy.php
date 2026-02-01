<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that need unique constraint updated from (code) to (company_id, code).
     */
    private array $tables = [
        'invoice_statuses',
        'order_statuses',
        'payment_methods',
        'product_groups',
        'product_types',
        'project_statuses',
        'project_types',
        'quote_statuses',
        'share_classes',
        'units',
        'vat_codes',
        'vat_rates',
        'work_order_priorities',
        'work_order_statuses',
        'work_order_types',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop old unique constraint on code only
                $table->dropUnique("{$tableName}_code_unique");

                // Add new composite unique constraint on company_id + code
                $table->unique(['company_id', 'code'], "{$tableName}_company_id_code_unique");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop composite unique constraint
                $table->dropUnique("{$tableName}_company_id_code_unique");

                // Restore original unique constraint on code only
                $table->unique('code', "{$tableName}_code_unique");
            });
        }
    }
};
