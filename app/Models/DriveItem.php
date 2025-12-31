<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriveItem extends Model
{
    /** @use HasFactory<\Database\Factories\DriveItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'google_account_id',
        'monitored_folder_id',
        'drive_file_id',
        'parent_drive_file_id',
        'name',
        'mime_type',
        'is_folder',
        'path_cache',
        'size_bytes',
        'md5_checksum',
        'modified_time',
        'created_time',
        'trashed',
        'starred',
        'owned_by_me',
        'owners_json',
        'owner_email',
        'owner_name',
        'last_modifier_email',
        'last_modifier_name',
        'permissions_json',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_folder' => 'boolean',
            'modified_time' => 'datetime',
            'created_time' => 'datetime',
            'trashed' => 'boolean',
            'starred' => 'boolean',
            'owned_by_me' => 'boolean',
            'owners_json' => 'array',
            'permissions_json' => 'array',
            'last_seen_at' => 'datetime',
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
