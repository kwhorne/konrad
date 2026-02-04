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
        Schema::create('a_melding_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('year');
            $table->integer('month');
            $table->enum('melding_type', ['ordinaer', 'tillegg', 'erstatning'])->default('ordinaer');
            $table->enum('status', ['draft', 'generated', 'submitted', 'confirmed', 'rejected'])->default('draft');

            $table->json('melding_data')->nullable();
            $table->longText('xml_content')->nullable();
            $table->string('altinn_reference')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_melding_reports');
    }
};
