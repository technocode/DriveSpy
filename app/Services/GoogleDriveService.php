<?php

namespace App\Services;

use App\Models\GoogleAccount;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    private Client $client;

    private Drive $driveService;

    private GoogleAccount $googleAccount;

    public function __construct(GoogleAccount $googleAccount)
    {
        $this->googleAccount = $googleAccount;
        $this->client = $this->initializeClient();
        $this->driveService = new Drive($this->client);
    }

    private function initializeClient(): Client
    {
        if (! $this->googleAccount->access_token || ! $this->googleAccount->refresh_token) {
            $this->googleAccount->update([
                'status' => 'error',
                'last_error' => 'Missing access or refresh token - please reconnect your Google account',
            ]);

            throw new \Exception('Google account tokens are missing. Please reconnect your account.');
        }

        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessToken([
            'access_token' => $this->googleAccount->access_token,
            'refresh_token' => $this->googleAccount->refresh_token,
            'expires_in' => $this->googleAccount->token_expires_at?->diffInSeconds(now()),
        ]);

        if ($client->isAccessTokenExpired() && $this->googleAccount->refresh_token) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($this->googleAccount->refresh_token);

            if (isset($newToken['error'])) {
                $this->googleAccount->update([
                    'status' => 'error',
                    'last_error' => $newToken['error_description'] ?? $newToken['error'] ?? 'Token refresh failed',
                ]);

                throw new \Exception('Failed to refresh access token: '.$newToken['error_description'] ?? $newToken['error']);
            }

            if (! isset($newToken['access_token'])) {
                $this->googleAccount->update([
                    'status' => 'error',
                    'last_error' => 'Token refresh returned invalid response',
                ]);

                throw new \Exception('Token refresh returned invalid response - no access_token');
            }

            $this->googleAccount->update([
                'access_token' => $newToken['access_token'],
                'token_expires_at' => now()->addSeconds($newToken['expires_in']),
            ]);
        }

        return $client;
    }

    public function getFolderContents(string $folderId, ?string $pageToken = null): array
    {
        $parameters = [
            'q' => "'{$folderId}' in parents and trashed=false",
            'fields' => 'nextPageToken, files(id, name, mimeType, size, md5Checksum, modifiedTime, createdTime, trashed, starred, ownedByMe, owners, parents, lastModifyingUser)',
            'pageSize' => 100,
        ];

        if ($pageToken) {
            $parameters['pageToken'] = $pageToken;
        }

        $results = $this->driveService->files->listFiles($parameters);

        return [
            'files' => $results->getFiles(),
            'nextPageToken' => $results->getNextPageToken(),
        ];
    }

    public function getFileMetadata(string $fileId): DriveFile
    {
        return $this->driveService->files->get($fileId, [
            'fields' => 'id, name, mimeType, size, md5Checksum, modifiedTime, createdTime, trashed, trashedTime, starred, ownedByMe, owners, parents, permissions, lastModifyingUser',
        ]);
    }

    public function getFileRevisions(string $fileId): array
    {
        try {
            $revisions = $this->driveService->revisions->listRevisions($fileId, [
                'fields' => 'revisions(id, modifiedTime, lastModifyingUser(emailAddress, displayName))',
                'pageSize' => 1, // Get only the latest revision
            ]);

            return $revisions->getRevisions() ?? [];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Files like folders, images, PDFs don't support revisions - this is expected
            if (str_contains($errorMessage, 'does not support revisions')) {
                logger()->debug('File does not support revisions (expected for folders, images, etc.)', [
                    'file_id' => $fileId,
                ]);
            } else {
                // Log actual errors at warning level
                logger()->warning('Failed to get file revisions', [
                    'file_id' => $fileId,
                    'error' => $errorMessage,
                ]);
            }

            return [];
        }
    }

    public function getLastModifier(string $fileId): ?array
    {
        $revisions = $this->getFileRevisions($fileId);

        if (empty($revisions)) {
            // Fallback: try to get from file metadata
            try {
                $file = $this->getFileMetadata($fileId);
                $lastModifyingUser = $file->getLastModifyingUser();

                if ($lastModifyingUser) {
                    return [
                        'email' => $lastModifyingUser->getEmailAddress(),
                        'name' => $lastModifyingUser->getDisplayName(),
                    ];
                }
            } catch (\Exception $e) {
                // Ignore errors
            }

            return null;
        }

        $latestRevision = $revisions[0];
        $lastModifyingUser = $latestRevision->getLastModifyingUser();

        if ($lastModifyingUser) {
            return [
                'email' => $lastModifyingUser->getEmailAddress(),
                'name' => $lastModifyingUser->getDisplayName(),
            ];
        }

        return null;
    }

    public function listAllFilesRecursively(string $folderId): array
    {
        $allFiles = [];
        $foldersToProcess = [$folderId];

        while (! empty($foldersToProcess)) {
            $currentFolder = array_shift($foldersToProcess);
            $pageToken = null;

            do {
                $result = $this->getFolderContents($currentFolder, $pageToken);
                $files = $result['files'];
                $pageToken = $result['nextPageToken'];

                foreach ($files as $file) {
                    $allFiles[] = $file;

                    if ($this->isFolder($file)) {
                        $foldersToProcess[] = $file->getId();
                    }
                }
            } while ($pageToken);
        }

        return $allFiles;
    }

    public function getStartPageToken(): string
    {
        $response = $this->driveService->changes->getStartPageToken();

        return $response->getStartPageToken();
    }

    public function getChanges(?string $pageToken = null): array
    {
        if (! $pageToken) {
            $pageToken = $this->getStartPageToken();
        }

        // Google Drive API v3 requires explicit field specification
        // Include all fields we need, especially 'parents' which is critical for hierarchy
        $fields = 'nextPageToken, newStartPageToken, changes(fileId, removed, changeType, time, file(id, name, mimeType, size, md5Checksum, modifiedTime, createdTime, trashed, starred, ownedByMe, owners, parents, lastModifyingUser, webViewLink, iconLink, hasThumbnail, thumbnailLink, shared, capabilities, permissions, spaces, version, webContentLink, headRevisionId, quotaBytesUsed, isAppAuthorized, description, explicitlyTrashed, teamDriveId, driveId))';

        $parameters = [
            'pageToken' => $pageToken,
            'fields' => $fields,
            'pageSize' => 100,
            'includeItemsFromAllDrives' => true,
            'supportsAllDrives' => true,
        ];

        logger()->info('Google Drive Changes API Request', [
            'page_token' => $pageToken,
            'fields_requested' => $fields,
            'parameters' => $parameters,
        ]);

        $results = $this->driveService->changes->listChanges($parameters);

        return [
            'changes' => $results->getChanges(),
            'nextPageToken' => $results->getNextPageToken(),
            'newStartPageToken' => $results->getNewStartPageToken(),
        ];
    }

    public function getFolders(string $folderId = 'root', ?string $pageToken = null): array
    {
        $parameters = [
            'q' => "'{$folderId}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'nextPageToken, files(id, name, mimeType, modifiedTime)',
            'pageSize' => 100,
            'orderBy' => 'name',
        ];

        if ($pageToken) {
            $parameters['pageToken'] = $pageToken;
        }

        $results = $this->driveService->files->listFiles($parameters);

        return [
            'folders' => $results->getFiles(),
            'nextPageToken' => $results->getNextPageToken(),
        ];
    }

    public function getFolderContentsWithFiles(string $folderId = 'root', ?string $pageToken = null): array
    {
        $parameters = [
            'q' => "'{$folderId}' in parents and trashed=false",
            'fields' => 'nextPageToken, files(id, name, mimeType, modifiedTime, size, iconLink)',
            'pageSize' => 100,
            'orderBy' => 'folder,name',
        ];

        if ($pageToken) {
            $parameters['pageToken'] = $pageToken;
        }

        $results = $this->driveService->files->listFiles($parameters);
        $items = $results->getFiles();

        // Separate folders and files
        $folders = [];
        $files = [];

        foreach ($items as $item) {
            if ($this->isFolder($item)) {
                $folders[] = $item;
            } else {
                $files[] = $item;
            }
        }

        return [
            'folders' => $folders,
            'files' => $files,
            'nextPageToken' => $results->getNextPageToken(),
        ];
    }

    public function isFolder(DriveFile $file): bool
    {
        return $file->getMimeType() === 'application/vnd.google-apps.folder';
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getDriveService(): Drive
    {
        return $this->driveService;
    }
}
