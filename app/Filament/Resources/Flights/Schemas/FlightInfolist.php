<?php

namespace App\Filament\Resources\Flights\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FlightInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('AWB Information')
                    ->schema([
                        TextEntry::make('awb')
                            ->label('AWB'),

                        TextEntry::make('type_awb')
                            ->label('Type AWB')
                            ->badge(),

                        TextEntry::make('logisticsCompany.name')
                            ->label('Logistics Company'),

                        TextEntry::make('airline.name')
                            ->label('Airline'),
                    ])
                    ->columns(2),

                Section::make('Dates')
                    ->schema([
                        TextEntry::make('date')
                            ->label('Flight Date')
                            ->date(),

                        TextEntry::make('arrival_date')
                            ->label('Arrival Date')
                            ->date(),
                    ])
                    ->columns(2),

                Section::make('Origin')
                    ->schema([
                        TextEntry::make('originCountry.name')
                            ->label('Country'),

                        TextEntry::make('originCity.name')
                            ->label('City'),
                    ])
                    ->columns(2),

                Section::make('Destination')
                    ->schema([
                        TextEntry::make('destinationCountry.name')
                            ->label('Country'),

                        TextEntry::make('destinationCity.name')
                            ->label('City'),
                    ])
                    ->columns(2),

                Section::make('Customs / Consignee')
                    ->schema([
                        TextEntry::make('consignee')
                            ->label('Consignee'),

                        TextEntry::make('entry_number')
                            ->label('Entry Number'),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        IconEntry::make('status')
                            ->label('Active')
                            ->boolean(),
                    ]),

                Section::make('Audit')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Created By'),

                        TextEntry::make('updater.name')
                            ->label('Updated By'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}