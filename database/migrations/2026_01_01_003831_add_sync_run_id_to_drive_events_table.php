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
        Schema::table('drive_events', function (Blueprint $table) {
            $table->foreignId('sync_run_id')->nullable()->after('monitored_folder_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drive_events', function (Blueprint $table) {
            $table->dropForeign(['sync_run_id']);
            $table->dropColumn('sync_run_id');
        });
    }
};
