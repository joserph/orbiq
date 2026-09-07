<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('state.name')
                    ->label('State')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city.name')
                    ->label('City')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('commercializers.name')
                    ->label('Commercializers')
                    ->badge()
                    ->separator(',')
                    ->limitList(3),

                TextColumn::make('load_types')
                    ->label('Load Types')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        if (blank($state)) {
                            return '';
                        }

                        if (is_string($state)) {
                            $decodedState = json_decode($state, true);

                            $state = is_array($decodedState)
                                ? $decodedState
                                : [$state];
                        }

                        if (! is_array($state)) {
                            $state = [$state];
                        }

                        return implode(
                            ', ',
                            array_map(
                                fn ($type) => match ($type) {
                                    'air' => 'Air',
                                    'maritime' => 'Maritime',
                                    default => ucfirst((string) $type),
                                },
                                $state
                            )
                        );
                    }),

                IconColumn::make('poa')
                    ->label('POA')
                    ->boolean(),

                IconColumn::make('status')
                    ->label('Status')
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
            ->recordAction('view')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->modal()
                    ->modalWidth('7xl')
                    ->modalHeading('Client Information'),
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
