<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drive_items', function (Blueprint $table) {
            $table->string('owner_email')->nullable()->after('owners_json');
            $table->string('owner_name')->nullable()->after('owner_email');
            $table->string('last_modifier_email')->nullable()->after('owner_name');
            $table->string('last_modifier_name')->nullable()->after('last_modifier_email');
        });
    }

    public function down(): void
    {
        Schema::table('drive_items', function (Blueprint $table) {
            $table->dropColumn(['owner_email', 'owner_name', 'last_modifier_email', 'last_modifier_name']);
        });
    }
};
