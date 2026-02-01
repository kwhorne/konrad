<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop empty legacy tables if they exist
        Schema::dropIfExists('stock_count_lines');
        Schema::dropIfExists('stock_counts');

        Schema::create('inventory_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('cogs_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('grni_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('inventory_adjustment_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('default_stock_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->boolean('auto_reserve_on_order')->default(true);
            $table->boolean('allow_negative_stock')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_settings');
    }
};
