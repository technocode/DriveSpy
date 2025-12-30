<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleAccount extends Model
{
    /** @use HasFactory<\Database\Factories\GoogleAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'google_user_id',
        'email',
        'display_name',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'drive_start_page_token',
        'last_synced_at',
        'status',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function monitoredFolders(): HasMany
    {
        return $this->hasMany(MonitoredFolder::class);
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
