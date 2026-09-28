<?php

namespace App\Filament\Resources\Flights\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FlightsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('awb')
                    ->label('AWB')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type_awb')
                    ->label('Type AWB')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'OWN' => 'Own AWB',
                        'EXTERNAL' => 'External AWB',
                        default => $state,
                    }),

                TextColumn::make('logisticsCompany.name')
                    ->label('Logistics Company')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('airline.name')
                    ->label('Airline')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Flight Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('arrival_date')
                    ->label('Arrival Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('originCity.name')
                    ->label('Origin')
                    ->searchable(),

                TextColumn::make('destinationCity.name')
                    ->label('Destination')
                    ->searchable(),

                TextColumn::make('consignee')
                    ->label('Consignee')
                    ->searchable(),

                TextColumn::make('entry_number')
                    ->label('Entry Number')
                    ->searchable(),

                IconColumn::make('status')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updater.name')
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
            ->recordActions([
                ViewAction::make()
                    ->modal()
                    ->modalHeading('Flight Information'),
                EditAction::make()
                    ->modal()
                    ->modalHeading('Edit Flight'),
                // Action::make('coordinations')
                //     ->label('Coordinations')
                //     ->icon('heroicon-o-clipboard-document-list')
                //     ->url(fn ($record) => \App\Filament\Resources\FlightCoordinations\FlightCoordinationResource::getUrl(
                //         'index'
                //     ) . '?flight=' . $record->id),
                Action::make('coordinations')
                    ->label('Coordinations')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn ($record) => \App\Filament\Resources\Flights\FlightResource::getUrl(
                        'coordinations',
                        ['record' => $record]
                    )),
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
