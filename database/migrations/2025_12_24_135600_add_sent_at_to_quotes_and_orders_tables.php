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
        Schema::table('quotes', function (Blueprint $table) {
            $table->datetime('sent_at')->nullable()->after('is_active');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->datetime('sent_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('sent_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('sent_at');
        });
    }
};
