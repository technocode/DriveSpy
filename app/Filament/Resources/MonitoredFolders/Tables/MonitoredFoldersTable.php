<?php

namespace App\Filament\Resources\MonitoredFolders\Tables;

use App\Jobs\InitialIndexJob;
use App\Jobs\ReindexFolderJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitoredFoldersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('root_name')
                    ->label('Folder Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('googleAccount.email')
                    ->label('Account')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'error' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('include_subfolders')
                    ->label('Subfolders')
                    ->boolean(),
                TextColumn::make('last_indexed_at')
                    ->label('Last Indexed')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                TextColumn::make('last_changed_at')
                    ->label('Last Changed')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
                TextColumn::make('root_drive_file_id')
                    ->label('Drive ID')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('initial_index')
                    ->label('Start Initial Index')
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Start Initial Index')
                    ->modalDescription('This will scan the entire folder structure and save all files to the database. This may take a while for large folders.')
                    ->modalSubmitActionLabel('Start Index')
                    ->action(function ($record) {
                        InitialIndexJob::dispatch($record);

                        $record->update(['status' => 'indexing']);

                        Notification::make()
                            ->title('Initial index started')
                            ->body('The folder indexing has been queued.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->last_indexed_at === null && $record->status !== 'indexing'),
                Action::make('reindex')
                    ->label('Reindex')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reindex Folder')
                    ->modalDescription('This will rescan the folder and update all file information. Existing data will be preserved.')
                    ->modalSubmitActionLabel('Reindex')
                    ->action(function ($record) {
                        ReindexFolderJob::dispatch($record);

                        Notification::make()
                            ->title('Reindex started')
                            ->body('The folder is being reindexed.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->last_indexed_at !== null && in_array($record->status, ['active', 'error'])),
                Action::make('toggle_status')
                    ->label(fn ($record) => $record->status === 'active' ? 'Pause' : 'Resume')
                    ->icon(fn ($record) => $record->status === 'active' ? Heroicon::OutlinedPause : Heroicon::OutlinedPlay)
                    ->color(fn ($record) => $record->status === 'active' ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->status === 'active' ? 'Pause Monitoring' : 'Resume Monitoring')
                    ->modalDescription(fn ($record) => $record->status === 'active'
                        ? 'This will stop monitoring this folder for changes.'
                        : 'This will resume monitoring this folder for changes.')
                    ->modalSubmitActionLabel(fn ($record) => $record->status === 'active' ? 'Pause' : 'Resume')
                    ->action(function ($record) {
                        $newStatus = $record->status === 'active' ? 'paused' : 'active';

                        $record->update(['status' => $newStatus]);

                        Notification::make()
                            ->title('Status updated')
                            ->body("Folder monitoring has been {$newStatus}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => in_array($record->status, ['active', 'paused'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
