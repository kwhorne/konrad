<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_flow_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annual_account_id')->constrained('annual_accounts')->cascadeOnDelete();

            // Kontantstrøm fra operasjonelle aktiviteter
            $table->decimal('profit_before_tax', 15, 2)->default(0);
            $table->decimal('tax_paid', 15, 2)->default(0);
            $table->decimal('depreciation', 15, 2)->default(0);
            $table->decimal('change_in_inventory', 15, 2)->default(0);
            $table->decimal('change_in_receivables', 15, 2)->default(0);
            $table->decimal('change_in_payables', 15, 2)->default(0);
            $table->decimal('other_operating_items', 15, 2)->default(0);
            $table->decimal('net_operating_cash_flow', 15, 2)->default(0);

            // Kontantstrøm fra investeringsaktiviteter
            $table->decimal('purchase_of_fixed_assets', 15, 2)->default(0);
            $table->decimal('sale_of_fixed_assets', 15, 2)->default(0);
            $table->decimal('purchase_of_investments', 15, 2)->default(0);
            $table->decimal('sale_of_investments', 15, 2)->default(0);
            $table->decimal('other_investing_items', 15, 2)->default(0);
            $table->decimal('net_investing_cash_flow', 15, 2)->default(0);

            // Kontantstrøm fra finansieringsaktiviteter
            $table->decimal('proceeds_from_borrowings', 15, 2)->default(0);
            $table->decimal('repayment_of_borrowings', 15, 2)->default(0);
            $table->decimal('share_capital_increase', 15, 2)->default(0);
            $table->decimal('dividends_paid', 15, 2)->default(0);
            $table->decimal('other_financing_items', 15, 2)->default(0);
            $table->decimal('net_financing_cash_flow', 15, 2)->default(0);

            // Netto endring
            $table->decimal('net_change_in_cash', 15, 2)->default(0);
            $table->decimal('opening_cash_balance', 15, 2)->default(0);
            $table->decimal('closing_cash_balance', 15, 2)->default(0);

            // Metadata
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('annual_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flow_statements');
    }
};
