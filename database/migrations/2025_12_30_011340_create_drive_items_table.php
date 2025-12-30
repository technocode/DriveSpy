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
        Schema::create('drive_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monitored_folder_id')->constrained()->cascadeOnDelete();
            $table->string('drive_file_id')->index();
            $table->string('parent_drive_file_id')->nullable()->index();
            $table->string('name');
            $table->string('mime_type')->index();
            $table->boolean('is_folder')->index();
            $table->text('path_cache')->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->string('md5_checksum')->nullable();
            $table->datetime('modified_time')->nullable();
            $table->datetime('created_time')->nullable();
            $table->boolean('trashed')->default(false);
            $table->boolean('starred')->default(false);
            $table->boolean('owned_by_me')->default(false);
            $table->json('owners_json')->nullable();
            $table->json('permissions_json')->nullable();
            $table->datetime('last_seen_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['google_account_id', 'drive_file_id']);
            $table->index(['monitored_folder_id', 'is_folder']);
            $table->index(['monitored_folder_id', 'modified_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_items');
    }
};
