<?php

namespace App\Filament\Resources\LogisticsCompanies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LogisticsCompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_type')
                    ->label('Company Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'AIRLINE' => 'Airline',
                        'SHIPPING_LINE' => 'Shipping Line',
                        'FREIGHT_FORWARDER' => 'Freight Forwarder',
                        'TRUCKING_COMPANY' => 'Trucking Company',
                        'CUSTOMS_BROKER' => 'Customs Broker',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('ruc')
                    ->label('RUC')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('web')
                    ->label('Website')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('country.name')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('state.name')
                    ->label('State')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city.name')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('status')
                    ->label('Active'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updater.name')
                    ->label('Updated By')
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
                ->filters([
                    //
                ])
                ->recordActions([
                    ViewAction::make()
                        ->modal()
                        ->modalWidth('7xl')
                        ->modalHeading('Logistics Companies Information'),
                    EditAction::make()
                        ->modal()
                        ->modalWidth('7xl')
                        ->modalHeading('Edit Logistics Companies'),
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
