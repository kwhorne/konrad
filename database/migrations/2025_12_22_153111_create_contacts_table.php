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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('contact_number')->unique();

            // Type & Basic Info
            $table->enum('type', [
                'customer',
                'supplier',
                'partner',
                'prospect',
                'competitor',
                'other',
            ])->default('customer');

            $table->string('company_name');
            $table->string('organization_number')->nullable();
            $table->string('industry')->nullable();
            $table->string('website')->nullable();

            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Norge');

            // Billing Address (if different)
            $table->string('billing_address')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_country')->nullable();

            // Business Details
            $table->enum('customer_category', [
                'a',
                'b',
                'c',
            ])->nullable();

            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->integer('payment_terms_days')->default(30);
            $table->string('payment_method')->nullable();
            $table->string('bank_account')->nullable();

            // Social Media & Communication
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();

            // Internal Info
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->json('attachments')->nullable();

            // Status & Tracking
            $table->enum('status', [
                'active',
                'inactive',
                'prospect',
                'archived',
            ])->default('active');

            $table->boolean('is_active')->default(true);
            $table->date('customer_since')->nullable();
            $table->date('last_contact_date')->nullable();

            // User Relations
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('account_manager_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('status');
            $table->index('company_name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
