<?php

namespace App\Filament\Resources\GoogleAccounts\Tables;

use App\Jobs\SyncChangesJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GoogleAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=G'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Display Name')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'revoked' => 'danger',
                        'error' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                TextColumn::make('token_expires_at')
                    ->label('Token Expires')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
                Action::make('sync')
                    ->label('Sync Now')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Google Drive')
                    ->modalDescription('This will check for changes in your Google Drive and update the database.')
                    ->modalSubmitActionLabel('Start Sync')
                    ->action(function ($record) {
                        SyncChangesJob::dispatch($record);

                        Notification::make()
                            ->title('Sync started')
                            ->body('Google Drive sync has been queued.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'active'),
                Action::make('disconnect')
                    ->label('Disconnect')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Disconnect Google Account')
                    ->modalDescription('Are you sure you want to disconnect this Google account? You can reconnect it later.')
                    ->modalSubmitActionLabel('Disconnect')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'revoked',
                            'access_token' => null,
                        ]);

                        Notification::make()
                            ->title('Account disconnected')
                            ->body('The Google account has been disconnected.')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'active'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
