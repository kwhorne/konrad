<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_account_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annual_account_id')->constrained('annual_accounts')->cascadeOnDelete();

            // Noteinformasjon
            $table->integer('note_number');
            $table->string('note_type', 50); // accounting_principles, employees, fixed_assets, etc.
            $table->string('title');
            $table->longText('content');

            // Strukturerte data (for spesifikke notetyper)
            $table->json('structured_data')->nullable();

            // Rekkefølge og visning
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['annual_account_id', 'note_number']);
            $table->index(['annual_account_id', 'note_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_account_notes');
    }
};
