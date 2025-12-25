<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_accounts', function (Blueprint $table) {
            $table->id();
            $table->year('fiscal_year');
            $table->date('period_start');
            $table->date('period_end');

            // Selskapsstørrelse (regnskapsloven § 1-5, 1-6)
            $table->enum('company_size', ['small', 'medium', 'large'])->default('small');

            // Nøkkeltall fra resultatregnskap
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('operating_profit', 15, 2)->default(0);
            $table->decimal('profit_before_tax', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);

            // Nøkkeltall fra balanse
            $table->decimal('total_assets', 15, 2)->default(0);
            $table->decimal('total_equity', 15, 2)->default(0);
            $table->decimal('total_liabilities', 15, 2)->default(0);

            // Ansatte
            $table->integer('average_employees')->default(0);

            // Revisor
            $table->string('auditor_name')->nullable();
            $table->string('auditor_org_number', 9)->nullable();
            $table->enum('audit_opinion', ['unqualified', 'qualified', 'adverse', 'disclaimer', 'not_required'])->nullable();
            $table->date('audit_date')->nullable();

            // Godkjenningsdatoer
            $table->date('board_approval_date')->nullable();
            $table->date('general_meeting_date')->nullable();

            // Status
            $table->enum('status', ['draft', 'approved', 'submitted', 'accepted', 'rejected'])->default('draft');

            // Altinn-innsending
            $table->foreignId('altinn_submission_id')->nullable()->constrained('altinn_submissions')->nullOnDelete();
            $table->string('altinn_reference')->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Metadata
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('fiscal_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_accounts');
    }
};
