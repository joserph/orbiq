<?php

namespace App\Filament\Resources\Loads\Schemas;

use App\Models\LogisticsCompany;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class LoadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del embarque')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('shipment')
                            ->label('Shipment')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Se genera automáticamente'),

                        TextInput::make('bl')
                            ->label('Bill of Lading (BL)')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),

                        TextInput::make('booking')
                            ->label('Booking')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('carrier')
                            ->label('Carrier / Naviera')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('date')
                            ->label('Fecha del embarque')
                            ->required()
                            ->default(now()),

                        DatePicker::make('arrival_date')
                            ->label('Fecha de llegada')
                            ->required()
                            // ->native(false)
                            ->afterOrEqual('date'),

                        TextInput::make('consignee')
                            ->label('Consignee')
                            ->maxLength(255),

                        TextInput::make('entry_number')
                            ->label('Entry Number')
                            ->maxLength(255),
                        Select::make('origin_country_id')
                            ->label('Origin Country')
                            ->required()
                            ->options(
                                Country::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->native(false)
                            ->afterStateUpdated(function (callable $set) {
                                $set('origin_city_id', null);
                            }),

                        Select::make('origin_city_id')
                            ->label('Origin City')
                            ->required()
                            ->options(function (callable $get) {
                                $countryId = $get('origin_country_id');

                                if (! $countryId) {
                                    return [];
                                }

                                return City::query()
                                    ->where('country_id', $countryId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => ! $get('origin_country_id'))
                            ->native(false),

                        Select::make('destination_country_id')
                            ->label('Destination Country')
                            ->required()
                            ->options(
                                Country::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->native(false)
                            ->afterStateUpdated(function (callable $set) {
                                $set('destination_city_id', null);
                            }),

                        Select::make('destination_city_id')
                            ->label('Destination City')
                            ->required()
                            ->options(function (callable $get) {
                                $countryId = $get('destination_country_id');

                                if (! $countryId) {
                                    return [];
                                }

                                return City::query()
                                    ->where('country_id', $countryId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => ! $get('destination_country_id'))
                            ->native(false),

                        Select::make('status')
                            ->label('Estatus')
                            ->options([
                                'pending' => 'Pendiente',
                                'in_transit' => 'En tránsito',
                                'arrived' => 'Llegado',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),

                Section::make('Información del contenedor')
                    ->columns(5)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('container_number')
                            ->label('Número de contenedor')
                            ->maxLength(255),

                        TextInput::make('seal_bottle')
                            ->label('Sello botella')
                            ->maxLength(255),

                        TextInput::make('seal_cable')
                            ->label('Sello cable')
                            ->maxLength(255),

                        TextInput::make('seal_sticker')
                            ->label('Sello sticker')
                            ->maxLength(255),

                        Select::make('floor')
                            ->label('¿Lleva piso?')
                            ->options([
                                'si' => 'Sí',
                                'no' => 'No',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state !== 'si') {
                                    $set('num_pallets', null);
                                }
                            }),

                        TextInput::make('num_pallets')
                            ->label('Número de pallets')
                            ->numeric()
                            ->minValue(0)
                            ->visible(fn ($get): bool => $get('floor') === 'si')
                            ->required(fn ($get): bool => $get('floor') === 'si'),

                        Select::make('id_logistic_company')
                            ->label('Empresa logística')
                            ->columnSpan(2)
                            ->required()
                            ->options(
                                fn () => LogisticsCompany::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),

                Section::make('Termógrafos')
                    ->description('Agrega uno o varios termógrafos para este contenedor.')
                    ->schema([
                        Repeater::make('thermographs')
                            ->label('Termógrafos')
                            ->schema([
                                TextInput::make('code_deep')
                                    ->label('Código')
                                    ->maxLength(50),

                                TextInput::make('brand_deep')
                                    ->label('Marca')
                                    ->maxLength(150),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar termógrafo')
                            ->reorderable()
                            ->collapsible(),
                    ]),

                Section::make('Tipos de caja habilitados')
                    ->description('Selecciona los tipos de caja permitidos para este contenedor.')
                    ->columns(5)
                    ->schema([
                        Toggle::make('fb_status')
                            ->label('FB')
                            ->default(false),

                        Toggle::make('hb_status')
                            ->label('HB')
                            ->default(true),

                        Toggle::make('qb_status')
                            ->label('QB')
                            ->default(true),

                        Toggle::make('eb_status')
                            ->label('EB')
                            ->default(true),

                        Toggle::make('db_status')
                            ->label('DB')
                            ->default(false),
                    ]),
            ]);
    }
}
