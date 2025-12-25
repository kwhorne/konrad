<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type')->default('alminnelig'); // alminnelig, primaer, etc.
            $table->string('period_type')->default('bimonthly'); // bimonthly, monthly, annual
            $table->integer('year');
            $table->integer('period'); // 1-6 for bimonthly, 1-12 for monthly
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('total_base', 14, 2)->default(0);
            $table->decimal('total_output_vat', 14, 2)->default(0); // Utgående MVA
            $table->decimal('total_input_vat', 14, 2)->default(0); // Inngående MVA
            $table->decimal('vat_payable', 14, 2)->default(0); // Beløp å betale (kan være negativt)
            $table->text('note')->nullable(); // Merknad
            $table->enum('status', ['draft', 'calculated', 'submitted', 'accepted', 'rejected'])->default('draft');
            $table->datetime('calculated_at')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->string('altinn_reference')->nullable(); // Referanse fra Altinn
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['year', 'period', 'period_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_reports');
    }
};
