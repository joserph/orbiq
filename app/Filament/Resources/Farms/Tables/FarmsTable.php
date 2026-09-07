<?php

namespace App\Filament\Resources\Farms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FarmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trade_name')
                    ->label('Trade Name')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('ruc')
                    ->label('RUC')
                    ->searchable(),
                TextColumn::make('flowerVarieties.name')
                    ->label('Flower Varieties')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('state.name')
                    ->label('State')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city.name')
                    ->label('City')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('status')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updatedBy.name')
                    ->label('Updated By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])

            ->recordActions([
                ViewAction::make()
                    ->modal()
                    ->modalWidth('7xl'),
                EditAction::make()
                    ->modal()
                    ->modalWidth('7xl'),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->recordUrl(fn ($record) => null);
    }
}
