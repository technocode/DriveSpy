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
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessToken([
            'access_token' => $this->googleAccount->access_token,
            'refresh_token' => $this->googleAccount->refresh_token,
            'expires_in' => $this->googleAccount->token_expires_at?->diffInSeconds(now()),
        ]);

        if ($client->isAccessTokenExpired() && $this->googleAccount->refresh_token) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($this->googleAccount->refresh_token);

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
            'fields' => 'nextPageToken, files(id, name, mimeType, size, md5Checksum, modifiedTime, createdTime, trashed, starred, ownedByMe, owners, parents)',
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
            'fields' => 'id, name, mimeType, size, md5Checksum, modifiedTime, createdTime, trashed, starred, ownedByMe, owners, parents, permissions',
        ]);
    }

    public function listAllFilesRecursively(string $folderId): array
    {
        $allFiles = [];
        $foldersToProcess = [$folderId];

        while (!empty($foldersToProcess)) {
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
        if (!$pageToken) {
            $pageToken = $this->getStartPageToken();
        }

        $parameters = [
            'pageToken' => $pageToken,
            'fields' => 'nextPageToken, newStartPageToken, changes(fileId, removed, file(id, name, mimeType, size, md5Checksum, modifiedTime, createdTime, trashed, starred, ownedByMe, owners, parents))',
            'pageSize' => 100,
        ];

        $results = $this->driveService->changes->listChanges($parameters);

        return [
            'changes' => $results->getChanges(),
            'nextPageToken' => $results->getNextPageToken(),
            'newStartPageToken' => $results->getNewStartPageToken(),
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
