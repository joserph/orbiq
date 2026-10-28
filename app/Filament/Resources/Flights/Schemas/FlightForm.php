<?php

namespace App\Filament\Resources\Flights\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class FlightForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Flight Configuration')
                    ->columns(5)
                    ->schema([
                        TextInput::make('awb')
                            ->label('AWB')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('type_awb')
                            ->label('Type AWB')
                            ->options([
                                'OWN' => 'Own AWB',
                                'EXTERNAL' => 'External AWB',
                            ])
                            ->required()
                            ->native(false),

                        DatePicker::make('date')
                            ->label('Flight Date')
                            ->required()
                            ->native(false),

                        DatePicker::make('arrival_date')
                            ->label('Arrival Date')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('date')
                            ->validationMessages([
                                'after_or_equal' => 'The arrival date must be on or after the flight date.',
                            ]),

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

                        Select::make('logistics_company_id')
                            ->label('Logistics Company')
                            ->required()
                            ->relationship('logisticsCompany', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpan(2)
                            ->native(false),

                        Select::make('airline_id')
                            ->label('Airline')
                            ->required()
                            ->relationship('airline', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpan(2)
                            ->native(false),

                        TextInput::make('consignee')
                            ->label('Consignee')
                            ->maxLength(255),

                        TextInput::make('entry_number')
                            ->label('Entry Number')
                            ->maxLength(255),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),
                    ])->columnSpanFull(),

                

                
                Section::make('Box Configuration')
                    ->description('Enable the box types available for this AWB.')
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

                    ])
                    ->columnSpanFull(),
                Section::make('Thermographs')
                    ->description('Register one or more thermographs assigned to this flight.')
                    ->schema([
                        Repeater::make('thermographs')
                            ->schema([
                                TextInput::make('thermographic_code')
                                    ->label('Thermographic code')
                                    ->maxLength(255),

                                TextInput::make('thermograph_brand')
                                    ->label('Thermograph brand')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add thermograph')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
