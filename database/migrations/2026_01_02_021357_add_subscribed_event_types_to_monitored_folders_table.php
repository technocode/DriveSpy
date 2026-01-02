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
        Schema::table('monitored_folders', function (Blueprint $table) {
            $table->json('subscribed_event_types')->nullable()->after('include_subfolders');
        });
    }

    public function down(): void
    {
        Schema::table('monitored_folders', function (Blueprint $table) {
            $table->dropColumn('subscribed_event_types');
        });
    }
};
