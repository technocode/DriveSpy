<?php

namespace App\Filament\Resources\SyncRuns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SyncRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('run_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'initial_index' => 'info',
                        'changes_sync' => 'success',
                        'reindex' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'partial' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                TextColumn::make('finished_at')
                    ->label('Finished')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
                TextColumn::make('items_scanned')
                    ->label('Scanned')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('changes_fetched')
                    ->label('Changes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('events_created')
                    ->label('Events')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('monitoredFolder.root_name')
                    ->label('Folder')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('googleAccount.email')
                    ->label('Account')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('started_at', 'desc')
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
