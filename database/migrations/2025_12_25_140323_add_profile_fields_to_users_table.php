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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('title')->nullable()->after('phone'); // Job title/position
            $table->boolean('is_active')->default(true)->after('is_admin');
            $table->string('invitation_token')->nullable()->after('remember_token');
            $table->timestamp('invited_at')->nullable()->after('invitation_token');
            $table->timestamp('invitation_accepted_at')->nullable()->after('invited_at');
            $table->timestamp('last_login_at')->nullable()->after('invitation_accepted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'title',
                'is_active',
                'invitation_token',
                'invited_at',
                'invitation_accepted_at',
                'last_login_at',
            ]);
        });
    }
};
