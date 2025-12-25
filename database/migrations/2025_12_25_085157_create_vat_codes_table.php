<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // salg_norge, kjop_norge, import, export, other
            $table->enum('direction', ['output', 'input']); // output = utgående, input = inngående
            $table->decimal('rate', 5, 2)->nullable(); // MVA-sats i prosent
            $table->boolean('affects_base')->default(true); // Om grunnlag skal vises
            $table->boolean('affects_tax')->default(true); // Om avgift skal beregnes
            $table->integer('sign')->default(1); // 1 for positiv, -1 for negativ (fradrag)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_codes');
    }
};
