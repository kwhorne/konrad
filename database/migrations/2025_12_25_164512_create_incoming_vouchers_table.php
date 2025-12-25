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
        Schema::create('incoming_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 20)->unique();
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size');
            $table->enum('source', ['upload', 'email'])->default('upload');

            // E-post informasjon
            $table->string('email_from')->nullable();
            $table->string('email_subject')->nullable();
            $table->timestamp('email_received_at')->nullable();

            // Status og parsing
            $table->enum('status', ['pending', 'parsing', 'parsed', 'attested', 'approved', 'posted', 'rejected'])->default('pending');
            $table->timestamp('parsed_at')->nullable();
            $table->json('parsed_data')->nullable();

            // AI-foreslåtte verdier
            $table->foreignId('suggested_supplier_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('suggested_invoice_number')->nullable();
            $table->date('suggested_invoice_date')->nullable();
            $table->date('suggested_due_date')->nullable();
            $table->decimal('suggested_total', 12, 2)->nullable();
            $table->decimal('suggested_vat_total', 12, 2)->nullable();
            $table->foreignId('suggested_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('confidence_score', 3, 2)->nullable();

            // Godkjenningsflyt
            $table->foreignId('attested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('attested_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Avvisning
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Opprettet leverandørfaktura
            $table->foreignId('supplier_invoice_id')->nullable()->constrained('supplier_invoices')->nullOnDelete();

            // Opplastet av
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indekser
            $table->index('status');
            $table->index('source');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_vouchers');
    }
};
