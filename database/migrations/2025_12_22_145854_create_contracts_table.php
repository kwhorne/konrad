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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();

            // Dates
            $table->date('established_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months')->nullable();
            $table->date('notice_date')->nullable();
            $table->integer('notice_period_days')->default(90);

            // Relations
            $table->string('company_name');
            $table->string('company_contact')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('department')->nullable();
            $table->string('group')->nullable();
            $table->string('asset_reference')->nullable();

            // Contract details
            $table->enum('type', [
                'service',
                'lease',
                'maintenance',
                'software',
                'insurance',
                'employment',
                'supplier',
                'other',
            ])->default('other');

            $table->enum('status', [
                'draft',
                'active',
                'expiring_soon',
                'expired',
                'terminated',
                'renewed',
            ])->default('draft');

            // Financial
            $table->decimal('value', 12, 2)->nullable();
            $table->string('currency', 3)->default('NOK');
            $table->enum('payment_frequency', [
                'monthly',
                'quarterly',
                'yearly',
                'one_time',
            ])->nullable();

            // Auto-renewal
            $table->boolean('auto_renewal')->default(false);
            $table->integer('renewal_period_months')->nullable();

            // Additional info
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();

            // User tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('type');
            $table->index('end_date');
            $table->index('company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
