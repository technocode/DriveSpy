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
        Schema::create('monitored_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_account_id')->constrained()->cascadeOnDelete();
            $table->string('root_drive_file_id')->index();
            $table->string('root_name');
            $table->boolean('include_subfolders')->default(true);
            $table->string('status')->default('active');
            $table->datetime('last_indexed_at')->nullable();
            $table->datetime('last_changed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['google_account_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitored_folders');
    }
};
