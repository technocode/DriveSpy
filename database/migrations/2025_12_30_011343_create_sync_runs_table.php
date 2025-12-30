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
        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monitored_folder_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('run_type')->index();
            $table->string('status')->index();
            $table->datetime('started_at')->index();
            $table->datetime('finished_at')->nullable();
            $table->integer('items_scanned')->default(0);
            $table->integer('changes_fetched')->default(0);
            $table->integer('events_created')->default(0);
            $table->string('next_page_token')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->index(['google_account_id', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
    }
};
