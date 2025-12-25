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
        Schema::create('shareholder_reports', function (Blueprint $table) {
            $table->id();
            $table->year('year')->unique();
            $table->date('report_date'); // Typically Dec 31
            $table->decimal('share_capital', 15, 2); // Aksjekapital
            $table->integer('total_shares');
            $table->integer('number_of_shareholders');
            $table->string('status')->default('draft'); // draft, ready, submitted
            $table->json('snapshot_data')->nullable(); // Complete snapshot at report time
            $table->json('changes_during_year')->nullable(); // Summary of changes
            $table->json('dividend_summary')->nullable(); // Dividends paid during year
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('altinn_submission_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shareholder_reports');
    }
};
