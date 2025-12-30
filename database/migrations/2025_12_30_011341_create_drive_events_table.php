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
        Schema::create('drive_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monitored_folder_id')->constrained()->cascadeOnDelete();
            $table->string('drive_file_id')->index();
            $table->string('event_type')->index();
            $table->string('change_source')->default('changes_api');
            $table->datetime('occurred_at')->index();
            $table->string('actor_email')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->string('summary')->nullable();
            $table->timestamps();

            $table->index(['monitored_folder_id', 'occurred_at']);
            $table->index(['drive_file_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_events');
    }
};
