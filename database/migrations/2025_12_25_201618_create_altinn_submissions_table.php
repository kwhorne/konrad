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
        Schema::create('altinn_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('submission_type'); // aksjonaerregister, skattemelding, arsregnskap
            $table->year('year');
            $table->nullableMorphs('submittable'); // Polymorphic to report entities
            $table->string('status')->default('draft'); // draft, validating, submitted, accepted, rejected, error
            $table->string('altinn_instance_id')->nullable();
            $table->string('altinn_reference')->nullable();
            $table->json('submission_data')->nullable(); // Snapshot of submitted data
            $table->json('validation_errors')->nullable();
            $table->json('altinn_response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['submission_type', 'year']);
            $table->index('status');
            $table->index('submission_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('altinn_submissions');
    }
};
