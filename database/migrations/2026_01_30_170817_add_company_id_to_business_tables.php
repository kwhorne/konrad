<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that need company_id for multi-tenancy.
     *
     * @var array<string>
     */
    private array $tables = [
        // CRM / Contacts
        'contacts',
        'contact_persons',

        // Products
        'products',
        'product_groups',
        'product_types',
        'units',
        'vat_rates',
        'vat_codes',

        // Projects
        'projects',
        'project_lines',
        'project_types',
        'project_statuses',

        // Work Orders
        'work_orders',
        'work_order_lines',
        'work_order_types',
        'work_order_statuses',
        'work_order_priorities',

        // Sales - Quotes
        'quotes',
        'quote_lines',
        'quote_statuses',

        // Sales - Orders
        'orders',
        'order_lines',
        'order_statuses',

        // Sales - Invoices
        'invoices',
        'invoice_lines',
        'invoice_payments',
        'invoice_statuses',
        'payment_methods',

        // Accounting
        'accounts',
        'vouchers',
        'voucher_lines',
        'incoming_vouchers',

        // Supplier / Purchases
        'supplier_invoices',
        'supplier_invoice_lines',
        'supplier_payments',

        // VAT
        'vat_reports',
        'vat_report_lines',
        'vat_report_attachments',

        // Shareholders
        'shareholders',
        'share_classes',
        'shareholdings',
        'share_transactions',
        'shareholder_reports',
        'dividends',

        // Tax
        'tax_returns',
        'tax_adjustments',
        'tax_depreciation_schedules',
        'deferred_tax_items',

        // Annual Accounts
        'annual_accounts',
        'annual_account_notes',
        'cash_flow_statements',

        // Other
        'contracts',
        'assets',
        'activities',
        'activity_types',
        'altinn_submissions',
        'altinn_certificates',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
                    $table->index('company_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropForeign([$tableName.'_company_id_foreign']);
                    $table->dropIndex([$tableName.'_company_id_index']);
                    $table->dropColumn('company_id');
                });
            }
        }
    }
};
