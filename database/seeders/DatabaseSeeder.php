<?php

namespace Database\Seeders;

use App\Models\DriveEvent;
use App\Models\DriveItem;
use App\Models\GoogleAccount;
use App\Models\MonitoredFolder;
use App\Models\SyncRun;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $googleAccount = GoogleAccount::factory()->create([
            'user_id' => $user->id,
            'email' => 'testdrive@gmail.com',
            'display_name' => 'Test Drive Account',
        ]);

        $monitoredFolder = MonitoredFolder::factory()->create([
            'google_account_id' => $googleAccount->id,
            'root_name' => 'Bahan Penulis RPH365',
        ]);

        DriveItem::factory()->count(5)->create([
            'google_account_id' => $googleAccount->id,
            'monitored_folder_id' => $monitoredFolder->id,
        ]);

        DriveEvent::factory()->count(3)->create([
            'google_account_id' => $googleAccount->id,
            'monitored_folder_id' => $monitoredFolder->id,
        ]);

        SyncRun::factory()->create([
            'google_account_id' => $googleAccount->id,
            'monitored_folder_id' => $monitoredFolder->id,
            'status' => 'success',
        ]);
    }
}
