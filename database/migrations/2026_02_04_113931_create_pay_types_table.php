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
        Schema::create('pay_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('code', 10);
            $table->string('name');
            $table->enum('category', [
                'fastlonn',
                'timelonn',
                'overtid',
                'bonus',
                'tillegg',
                'trekk',
                'naturalytelse',
                'utgiftsgodtgjorelse',
            ]);

            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_aga_basis')->default(true);
            $table->boolean('is_vacation_basis')->default(true);
            $table->boolean('is_otp_basis')->default(true);

            $table->decimal('default_rate', 10, 2)->nullable();
            $table->decimal('overtid_faktor', 4, 2)->nullable();

            $table->string('a_melding_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_types');
    }
};
