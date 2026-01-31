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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('organization_number', 9)->unique();
            $table->string('vat_number')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country')->default('Norge');
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account', 20)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('swift', 11)->nullable();
            $table->string('logo_path')->nullable();
            $table->text('invoice_terms')->nullable();
            $table->text('quote_terms')->nullable();
            $table->integer('default_payment_days')->default(14);
            $table->integer('default_quote_validity_days')->default(30);
            $table->text('document_footer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
