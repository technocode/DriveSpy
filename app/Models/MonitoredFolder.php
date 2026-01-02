<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoredFolder extends Model
{
    /** @use HasFactory<\Database\Factories\MonitoredFolderFactory> */
    use HasFactory;

    protected $fillable = [
        'google_account_id',
        'root_drive_file_id',
        'root_name',
        'include_subfolders',
        'subscribed_event_types',
        'status',
        'last_indexed_at',
        'last_changed_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'include_subfolders' => 'boolean',
            'subscribed_event_types' => 'array',
            'last_indexed_at' => 'datetime',
            'last_changed_at' => 'datetime',
        ];
    }

    public function getAvailableEventTypes(): array
    {
        return [
            'created' => 'File Created',
            'updated' => 'File Updated',
            'deleted' => 'File Deleted',
            'trashed' => 'File Trashed',
            'restored' => 'File Restored',
            'renamed' => 'File Renamed',
            'moved' => 'File Moved',
            'metadata_changed' => 'Metadata Changed',
        ];
    }

    public function isEventTypeSubscribed(string $eventType): bool
    {
        if (empty($this->subscribed_event_types)) {
            return true;
        }

        return in_array($eventType, $this->subscribed_event_types);
    }

    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleAccount::class);
    }

    public function driveItems(): HasMany
    {
        return $this->hasMany(DriveItem::class);
    }

    public function driveEvents(): HasMany
    {
        return $this->hasMany(DriveEvent::class);
    }

    public function syncRuns(): HasMany
    {
        return $this->hasMany(SyncRun::class);
    }
}
