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
        Schema::create('shareholdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shareholder_id')->constrained()->cascadeOnDelete();
            $table->foreignId('share_class_id')->constrained()->cascadeOnDelete();
            $table->integer('number_of_shares');
            $table->decimal('acquisition_cost', 15, 2)->nullable(); // Inngangsverdi total
            $table->decimal('cost_per_share', 15, 4)->nullable(); // Inngangsverdi per aksje
            $table->date('acquired_date');
            $table->string('acquisition_type'); // foundation, purchase, inheritance, gift, bonus, split
            $table->string('acquisition_reference')->nullable(); // Reference to transaction
            $table->date('disposed_date')->nullable();
            $table->string('disposal_type')->nullable(); // sale, redemption, merger
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['shareholder_id', 'share_class_id']);
            $table->index('is_active');
            $table->index('acquired_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shareholdings');
    }
};
