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
        Schema::create('holiday_pay_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->integer('opptjeningsaar');
            $table->decimal('grunnlag', 14, 2)->default(0);
            $table->decimal('opptjent', 12, 2)->default(0);
            $table->decimal('utbetalt', 12, 2)->default(0);
            $table->decimal('gjenstaaende', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['company_id', 'user_id', 'opptjeningsaar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_pay_balances');
    }
};
