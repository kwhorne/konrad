<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_report_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vat_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vat_code_id')->constrained()->restrictOnDelete();
            $table->decimal('base_amount', 14, 2)->default(0); // Grunnlag
            $table->decimal('vat_rate', 5, 2)->nullable(); // MVA-sats brukt
            $table->decimal('vat_amount', 14, 2)->default(0); // Avgift
            $table->text('note')->nullable(); // Linjemerknad
            $table->boolean('is_manual_override')->default(false); // Om beløp er manuelt justert
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['vat_report_id', 'vat_code_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_report_lines');
    }
};
