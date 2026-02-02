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
        Schema::create('csv_format_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('bank_name')->nullable();
            $table->char('delimiter', 1)->default(';');
            $table->string('encoding', 20)->default('UTF-8');
            $table->string('date_format', 20)->default('d.m.Y');
            $table->boolean('has_header')->default(true);
            $table->json('column_mapping');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index('company_id');
            $table->index('is_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csv_format_mappings');
    }
};
