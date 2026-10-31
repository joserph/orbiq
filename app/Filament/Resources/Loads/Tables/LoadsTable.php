<?php

namespace App\Filament\Resources\Loads\Tables;

use App\Filament\Resources\Loads\LoadResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shipment')
                    ->label('N°')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bl')
                    ->label('Bill of Lading')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin BL'),

                TextColumn::make('booking')
                    ->label('Booking')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('container_number')
                    ->label('Contenedor')
                    ->searchable()
                    ->placeholder('Sin número')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('carrier')
                    ->label('Naviera')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date')
                    ->label('Fecha Salida')
                    ->date()
                    ->sortable(),

                TextColumn::make('arrival_date')
                    ->label('Fecha llegada')
                    ->date()
                    ->sortable()
                    ->placeholder('Sin fecha'),
                ViewColumn::make('travel_progress')
                    ->label('Estatus del viaje')
                    ->view('filament.tables.columns.travel-progress')
                    ->extraAttributes([
                        'class' => 'min-w-[190px]',
                    ]),

                TextColumn::make('logisticsCompany.name')
                    ->label('Empresa logística')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin asignar')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('consignee')
                    ->label('Consignee')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('entry_number')
                    ->label('Entry Number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('originCity.name')
                    ->label('Origin')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('destinationCity.name')
                    ->label('Destination')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('thermographs')
                    ->label('Termógrafos')
                    ->state(fn ($record): int => count($record->thermographs ?? []))
                    ->formatStateUsing(fn (int $state): string =>
                        $state === 1
                            ? '1 termógrafo'
                            : "{$state} termógrafos"
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('floor')
                    ->label('Piso')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'si' => 'Sí',
                        'no' => 'No',
                        default => '—',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('num_pallets')
                    ->label('Pallets')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'in_transit' => 'En tránsito',
                        'arrived' => 'Llegado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $state ?? 'Sin estado',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_transit' => 'info',
                        'arrived' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estatus')
                    ->options([
                        'pending' => 'Pendiente',
                        'in_transit' => 'En tránsito',
                        'arrived' => 'Llegado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),

                SelectFilter::make('floor')
                    ->label('Piso')
                    ->options([
                        'si' => 'Sí',
                        'no' => 'No',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->modal()
                    ->modalWidth('7xl'),
                
                Action::make('coordinations')
                    ->label('Coordinaciones')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->url(fn ($record): string => LoadResource::getUrl(
                        'coordinations',
                        ['record' => $record]
                    )),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('date', 'desc');
    }
}
