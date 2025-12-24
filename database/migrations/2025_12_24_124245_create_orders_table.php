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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // Relationships
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_status_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Dates
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->string('customer_reference')->nullable();
            $table->integer('payment_terms_days')->default(14);

            // Terms and notes
            $table->text('terms_conditions')->nullable();
            $table->text('internal_notes')->nullable();

            // Customer snapshot
            $table->string('customer_name')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_postal_code', 20)->nullable();
            $table->string('customer_city')->nullable();
            $table->string('customer_country')->nullable();

            // Delivery address
            $table->text('delivery_address')->nullable();
            $table->string('delivery_postal_code', 20)->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_country')->nullable();

            // Totals
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('vat_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

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
        Schema::dropIfExists('orders');
    }
};
