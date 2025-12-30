<?php

namespace App\Filament\Resources\DriveEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DriveEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_type')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'moved' => 'warning',
                        'renamed' => 'warning',
                        'trashed' => 'danger',
                        'restored' => 'success',
                        'permission_changed' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('drive_file_id')
                    ->label('File ID')
                    ->searchable()
                    ->limit(20),
                TextColumn::make('summary')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('occurred_at')
                    ->label('Occurred')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                TextColumn::make('actor_email')
                    ->label('Actor')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('monitoredFolder.root_name')
                    ->label('Folder')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('change_source')
                    ->label('Source')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
