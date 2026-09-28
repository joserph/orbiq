<?php

namespace App\Filament\Resources\FlightCoordinations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FlightCoordinationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ==========================================
                // IDENTIFICATION
                // ==========================================

                TextColumn::make('hawb')
                    ->label('HAWB')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('farm.name')
                    ->label('Farm')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('marketer.name')
                    ->label('Commercializer')
                    ->searchable()
                    ->sortable(),

                // ==========================================
                // COORDINATED
                // ==========================================

                ColumnGroup::make('COORDINATED', [

                    TextColumn::make('fb')
                        ->label('FB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('hb')
                        ->label('HB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('qb')
                        ->label('QB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('eb')
                        ->label('EB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('db')
                        ->label('DB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('fulls')
                        ->label('FULLS')
                        ->numeric(decimalPlaces: 3)
                        ->sortable(),

                    TextColumn::make('pieces')
                        ->label('PIECES')
                        ->numeric()
                        ->sortable(),

                ]),

                // ==========================================
                // RECEIVED
                // ==========================================

                ColumnGroup::make('RECEIVED', [

                    TextColumn::make('fb_r')
                        ->label('FB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('hb_r')
                        ->label('HB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('qb_r')
                        ->label('QB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('eb_r')
                        ->label('EB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('db_r')
                        ->label('DB')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('fulls_r')
                        ->label('FULLS')
                        ->numeric(decimalPlaces: 3)
                        ->sortable(),

                    TextColumn::make('pieces_r')
                        ->label('PIECES')
                        ->numeric()
                        ->sortable(),

                ]),

                // ==========================================
                // CONTROL
                // ==========================================

                ColumnGroup::make('CONTROL', [

                    TextColumn::make('missing')
                        ->label('MISSING')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('returns')
                        ->label('RETURNS')
                        ->numeric()
                        ->sortable(),

                ]),

                // ==========================================
                // AUDIT
                // ==========================================

                TextColumn::make('created_by')
                    ->label('Created By')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_by')
                    ->label('Updated By')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([])

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}