# DriveSpy

A powerful Google Drive monitoring and activity tracking application built with Laravel 12 and Filament 4.

## 📋 Overview

DriveSpy monitors your Google Drive folders and tracks all file activities including creates, updates, moves, renames, deletions, and more. Perfect for auditing, compliance, team collaboration oversight, or personal file tracking.

## ✨ Features

- **Google OAuth Integration** - Secure authentication with Google accounts
- **Folder Monitoring** - Track multiple Google Drive folders per account
- **Automatic Indexing** - Initial scan and continuous change detection
- **Event Tracking** - Detailed logs of all file activities:
  - Created, Updated, Renamed
  - Moved, Trashed, Deleted, Restored
  - Metadata changes
- **Smart Sync** - Incremental sync using Google Drive Changes API
- **Manual Controls** - Pause/resume monitoring, force reindex
- **Beautiful Admin UI** - Built with Filament 4 for modern UX
- **Background Processing** - Queue-based jobs for scalability
- **Automatic Token Refresh** - Seamless OAuth token management

## 🛠️ Tech Stack

- **Laravel 12** - PHP framework
- **Filament 4** - Admin panel framework
- **Livewire 3** - Dynamic UI components
- **Flux UI** - Component library
- **Google Drive API v3** - Drive integration
- **MySQL/PostgreSQL** - Database
- **Queue System** - Background job processing

## 📦 Requirements

- PHP 8.3+
- Composer
- Node.js & NPM
- MySQL 8.0+ or PostgreSQL
- Google Cloud Project with Drive API enabled
- Laravel Herd/Valet (for local development) or web server

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/drivespy.git
cd drivespy
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=drivespy
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Setup

```bash
php artisan migrate --seed
```

**Default credentials:**
- Email: `test@example.com`
- Password: `password`

### 5. Build Assets

```bash
npm run build
# OR for development
npm run dev
```

### 6. Google OAuth Setup

Follow the detailed guide in [GOOGLE_CLOUD_SETUP.md](GOOGLE_CLOUD_SETUP.md) to:

1. Create a Google Cloud Project
2. Enable Google Drive API
3. Configure OAuth consent screen
4. Create OAuth 2.0 credentials
5. Set authorized redirect URIs

Then update `.env`:

```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

**For local development with ngrok:**

```bash
ngrok http drivespy.test:80
# Use the ngrok URL in your Google OAuth and .env
```

### 7. Start Queue Worker

DriveSpy uses queued jobs for syncing. Start the worker:

```bash
php artisan queue:work
```

### 8. Access the Application

```bash
# If using Laravel Herd
open http://drivespy.test/admin

# Or your configured URL
open http://localhost/admin
```

## 📖 Usage

### Connect Google Account

1. Navigate to **Configuration > Google Accounts**
2. Click **"Connect Google Account"**
3. Authorize DriveSpy to access your Google Drive
4. Account will appear in the list with "active" status

### Add Monitored Folder

1. Go to **Configuration > Monitored Folders**
2. Click **"Create"**
3. Fill in:
   - **Folder Name** - Display name
   - **Google Account** - Select connected account
   - **Root Drive File ID** - The folder ID from Google Drive URL
   - **Include Subfolders** - Toggle recursive monitoring
4. Save the folder

**Getting Folder ID:**
Open the folder in Google Drive, the URL will be:
```
https://drive.google.com/drive/folders/1AbC123XyZ456...
                                          ^^^^^^^^^^^^^^^^
                                          This is the Folder ID
```

### Start Initial Index

1. In **Monitored Folders**, click the actions menu (•••)
2. Select **"Start Initial Index"**
3. Job will be queued and processed by the worker
4. Monitor progress in **System > Sync Runs**

### View File Activity

1. Navigate to **Drive Data > Drive Items** - See all tracked files
2. Navigate to **Drive Data > Drive Events** - See activity log
3. Use filters and search to find specific events

### Sync Changes

DriveSpy automatically tracks changes using the Google Drive Changes API.

**Manual sync:**
1. Go to **Google Accounts**
2. Click **"Sync Now"** on any active account
3. New changes will be detected and logged

## 🗂️ Database Schema

```
users (Laravel default)
  └─ google_accounts (1:many)
      ├─ monitored_folders (1:many)
      │   ├─ drive_items (1:many)
      │   ├─ drive_events (1:many)
      │   └─ sync_runs (1:many)
      ├─ drive_items (1:many)
      ├─ drive_events (1:many)
      └─ sync_runs (1:many)

app_settings (standalone)
```

## 🔄 How It Works

### Initial Indexing
1. User adds a monitored folder
2. `InitialIndexJob` scans entire folder tree recursively
3. All files/folders saved to `drive_items` table
4. Start page token saved for future change tracking

### Change Detection
1. `SyncChangesJob` runs periodically (or manually)
2. Fetches changes since last page token
3. Compares with existing items
4. Creates `drive_events` for detected changes
5. Updates `drive_items` with new metadata
6. Saves new page token

### Event Types Tracked
- `created` - New file/folder added
- `updated` - File content changed
- `renamed` - Name changed
- `moved` - Parent folder changed
- `trashed` - Moved to trash
- `deleted` - Permanently deleted
- `restored` - Restored from trash
- `metadata_changed` - Other metadata updates

## 🎨 Filament Actions

### Google Accounts
- **Connect Google Account** - OAuth flow
- **Sync Now** - Manual change detection
- **Disconnect** - Revoke access

### Monitored Folders
- **Start Initial Index** - First-time scan
- **Reindex** - Full refresh
- **Pause/Resume** - Toggle monitoring

## ⚙️ Optional: Scheduled Tasks

Add to `routes/console.php` for automatic syncing:

```php
use App\Jobs\SyncChangesJob;
use App\Models\GoogleAccount;

Schedule::call(function () {
    GoogleAccount::where('status', 'active')->each(function ($account) {
        SyncChangesJob::dispatch($account);
    });
})->hourly();
```

Then run the scheduler:

```bash
php artisan schedule:work
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ExampleTest
```

## 📝 Development Notes

- All OAuth tokens are encrypted in the database
- Files use soft deletes for data retention
- Sync runs log all operations for debugging
- Error messages stored on accounts and folders
- Uses Laravel's queue system for scalability

## 🔒 Security

- OAuth tokens encrypted at rest
- CSRF protection on all forms
- Authentication required for all admin routes
- Rate limiting on API calls
- Proper authorization checks

## 🚧 Known Limitations (MVP)

- No real-time webhooks (polling via Changes API)
- No file content search (metadata only)
- Manual folder ID entry (no picker UI yet)
- Single-tenant (one app instance)
- No email notifications

## 🛣️ Roadmap

- [ ] Real-time webhooks for instant updates
- [ ] File content full-text search
- [ ] Visual folder picker UI
- [ ] Email notifications for events
- [ ] Multi-tenant support
- [ ] Artisan commands for management
- [ ] API endpoints for external integration
- [ ] Export events to CSV/PDF
- [ ] Dashboard with charts and stats

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Filament](https://filamentphp.com) - Admin Panel Framework
- [Google Drive API](https://developers.google.com/drive) - Drive Integration
- [Laravel Herd](https://herd.laravel.com) - Local Development Environment

## 📧 Support

If you encounter any issues or have questions:

1. Check [GOOGLE_CLOUD_SETUP.md](GOOGLE_CLOUD_SETUP.md) for OAuth setup help
2. Review [IMPLEMENTATION_STATUS.md](IMPLEMENTATION_STATUS.md) for development status
3. Open an issue on GitHub
4. Review Laravel and Filament documentation

---

**Built with ❤️ using Laravel and Filament**
