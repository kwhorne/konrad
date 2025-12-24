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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->enum('invoice_type', ['invoice', 'credit_note'])->default('invoice');
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // Relationships
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('original_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('invoice_status_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Dates and payment terms
            $table->date('invoice_date');
            $table->date('due_date');
            $table->integer('payment_terms_days')->default(14);
            $table->integer('reminder_days')->default(14);
            $table->date('reminder_date')->nullable();

            // Status tracking
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('paid_at')->nullable();

            // Terms and notes
            $table->text('terms_conditions')->nullable();
            $table->text('internal_notes')->nullable();

            // Customer snapshot
            $table->string('customer_name')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_postal_code', 20)->nullable();
            $table->string('customer_city')->nullable();
            $table->string('customer_country')->nullable();

            // Our company info snapshot
            $table->string('our_reference')->nullable();
            $table->string('customer_reference')->nullable();

            // Totals
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('vat_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);

            // Metadata
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
