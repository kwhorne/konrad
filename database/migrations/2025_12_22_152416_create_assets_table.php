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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();

            // Basic info
            $table->string('serial_number')->nullable();
            $table->string('asset_model')->nullable();

            // Purchase info
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('NOK');
            $table->date('purchase_date')->nullable();

            // Supplier/Vendor
            $table->string('supplier')->nullable();
            $table->string('manufacturer')->nullable();

            // Location & Organization
            $table->string('location')->nullable();
            $table->string('department')->nullable();
            $table->string('group')->nullable();

            // Insurance & Invoice
            $table->string('insurance_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            // Warranty
            $table->date('warranty_from')->nullable();
            $table->date('warranty_until')->nullable();

            // Status & Condition
            $table->enum('status', [
                'in_use',
                'available',
                'maintenance',
                'retired',
                'lost',
                'sold',
            ])->default('available');

            $table->enum('condition', [
                'excellent',
                'good',
                'fair',
                'poor',
                'broken',
            ])->default('good');

            $table->boolean('is_active')->default(true);

            // Notes & Attachments
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->json('images')->nullable();

            // User tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('location');
            $table->index('department');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
