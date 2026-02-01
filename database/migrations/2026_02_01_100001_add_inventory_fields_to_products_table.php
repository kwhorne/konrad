<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_stocked')->default(false)->after('cost_price');
            $table->decimal('reorder_point', 12, 2)->nullable()->after('is_stocked');
            $table->decimal('reorder_quantity', 12, 2)->nullable()->after('reorder_point');
            $table->foreignId('default_stock_location_id')->nullable()->after('reorder_quantity')
                ->constrained('stock_locations')->nullOnDelete();

            $table->index('is_stocked');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['default_stock_location_id']);
            $table->dropIndex(['is_stocked']);
            $table->dropColumn(['is_stocked', 'reorder_point', 'reorder_quantity', 'default_stock_location_id']);
        });
    }
};
