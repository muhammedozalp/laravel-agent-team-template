<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Developer tier (ADR 0011): sees the checklists page; granted only
            // via `php artisan app:make-admin --developer`. Never fillable.
            $table->boolean('is_developer')->default(false)->after('is_admin');
        });

        // Per-project checklist STATE — definitions live in config/checklists.php.
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->timestampTz('checked_at')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('last_result')->nullable();   // auto probes: pass/fail
            $table->timestampTz('last_run_at')->nullable();
            $table->text('detail')->nullable();           // probe output / notes
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_developer');
        });
    }
};
