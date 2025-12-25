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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // Company information
            $table->string('company_name');
            $table->string('organization_number')->nullable();
            $table->string('vat_number')->nullable(); // MVA-nummer

            // Address
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Norge');

            // Contact information
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Bank information
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable(); // Kontonummer
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();

            // Logo
            $table->string('logo_path')->nullable();

            // Document settings
            $table->text('invoice_terms')->nullable(); // Standard betalingsbetingelser
            $table->text('quote_terms')->nullable(); // Standard tilbudsbetingelser
            $table->text('order_terms')->nullable(); // Standard ordrebetingelser
            $table->integer('default_payment_days')->default(14);
            $table->integer('default_quote_validity_days')->default(30);

            // Footer text for documents
            $table->text('document_footer')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
