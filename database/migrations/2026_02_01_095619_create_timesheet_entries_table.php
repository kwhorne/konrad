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
        Schema::create('timesheet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timesheet_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->decimal('hours', 5, 2);
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['timesheet_id', 'entry_date']);
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_entries');
    }
};
