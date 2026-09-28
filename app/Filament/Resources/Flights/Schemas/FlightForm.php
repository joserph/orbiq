<?php

namespace App\Filament\Resources\Flights\Schemas;

use Filament\Forms\Components\DatePicker;
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
                TextInput::make('awb')
                    ->label('AWB')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('type_awb')
                    ->label('Type AWB')
                    ->options([
                        'OWN' => 'Own AWB',
                        'EXTERNAL' => 'External AWB',
                    ])
                    ->required()
                    ->native(false),

                Select::make('logistics_company_id')
                    ->label('Logistics Company')
                    ->relationship('logisticsCompany', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Select::make('airline_id')
                    ->label('Airline')
                    ->relationship('airline', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                DatePicker::make('date')
                    ->label('Flight Date')
                    ->native(false),

                DatePicker::make('arrival_date')
                    ->label('Arrival Date')
                    ->native(false),

                Select::make('origin_country_id')
                    ->label('Origin Country')
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

                TextInput::make('consignee')
                    ->label('Consignee')
                    ->maxLength(255),

                TextInput::make('entry_number')
                    ->label('Entry Number')
                    ->maxLength(255),

                Toggle::make('status')
                    ->label('Active')
                    ->default(true),
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
                
            ]);
    }
}
