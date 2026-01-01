<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriveEvent extends Model
{
    /** @use HasFactory<\Database\Factories\DriveEventFactory> */
    use HasFactory;

    protected $fillable = [
        'google_account_id',
        'monitored_folder_id',
        'sync_run_id',
        'drive_file_id',
        'event_type',
        'change_source',
        'occurred_at',
        'actor_email',
        'actor_name',
        'before_json',
        'after_json',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'before_json' => 'array',
            'after_json' => 'array',
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

    public function syncRun(): BelongsTo
    {
        return $this->belongsTo(SyncRun::class);
    }
}
