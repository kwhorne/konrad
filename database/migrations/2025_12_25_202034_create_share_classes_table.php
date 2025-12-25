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
        Schema::create('share_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // A-aksjer, B-aksjer, etc.
            $table->string('code')->unique(); // A, B, C...
            $table->string('isin')->nullable(); // ISIN code
            $table->decimal('par_value', 15, 2); // Pålydende
            $table->integer('total_shares')->default(0);
            $table->boolean('has_voting_rights')->default(true);
            $table->boolean('has_dividend_rights')->default(true);
            $table->decimal('voting_weight', 5, 2)->default(1.00); // Votes per share
            $table->text('restrictions')->nullable(); // JSON with restrictions
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_classes');
    }
};
