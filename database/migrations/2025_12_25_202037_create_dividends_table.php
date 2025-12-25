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
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->year('fiscal_year');
            $table->date('declaration_date'); // Vedtaksdato
            $table->date('record_date'); // Utbytteregistreringsdato
            $table->date('payment_date');
            $table->foreignId('share_class_id')->constrained();
            $table->decimal('amount_per_share', 15, 4);
            $table->decimal('total_amount', 15, 2);
            $table->string('dividend_type'); // ordinary, extraordinary
            $table->string('status')->default('declared'); // declared, approved, paid, cancelled
            $table->text('description')->nullable();
            $table->string('resolution_reference')->nullable(); // Protokoll/vedtak ref
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('fiscal_year');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dividends');
    }
};
