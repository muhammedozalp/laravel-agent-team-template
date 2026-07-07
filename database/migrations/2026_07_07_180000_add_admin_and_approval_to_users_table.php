<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Admin flag (deliberately NOT mass-assignable — see User model).
            $table->boolean('is_admin')->default(false);

            // Optional approval gate (config auth.require_approval); null = pending.
            $table->timestampTz('approved_at')->nullable();

            // Filament panel MFA — independent of Fortify's two_factor_* columns
            // by design, so the two login flows never collide.
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_admin',
                'approved_at',
                'app_authentication_secret',
                'app_authentication_recovery_codes',
            ]);
        });
    }
};
