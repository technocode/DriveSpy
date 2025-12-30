<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRun extends Model
{
    /** @use HasFactory<\Database\Factories\SyncRunFactory> */
    use HasFactory;

    protected $fillable = [
        'google_account_id',
        'monitored_folder_id',
        'run_type',
        'status',
        'started_at',
        'finished_at',
        'items_scanned',
        'changes_fetched',
        'events_created',
        'next_page_token',
        'error_message',
        'meta_json',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'meta_json' => 'array',
        ];
    }

    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleAccount::class);
    }

    public function monitoredFolder(): BelongsTo
    {
        return $this->belongsTo(MonitoredFolder::class);
    }
}
