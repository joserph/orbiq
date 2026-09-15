<?php

namespace App\Filament\Resources\Airlines\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;

class AirlineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Airline Name')
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(
                                fn ($state) => filled($state)
                                    ? mb_strtoupper($state)
                                    : $state
                            ),

                        TextInput::make('web')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('ruc')
                            ->label('RUC')
                            ->maxLength(50),

                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        Select::make('country_id')
                            ->label('Country')
                            ->options(
                                Country::query()
                                    ->where('status', 1)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('state_id', null);
                                $set('city_id', null);
                            }),

                        Select::make('state_id')
                            ->label('State')
                            ->options(function (callable $get) {
                                $countryId = $get('country_id');

                                if (! $countryId) {
                                    return [];
                                }

                                return State::query()
                                    ->where('country_id', $countryId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (callable $get) => ! $get('country_id'))
                            ->afterStateUpdated(function (callable $set) {
                                $set('city_id', null);
                            }),

                        Select::make('city_id')
                            ->label('City')
                            ->options(function (callable $get) {
                                $stateId = $get('state_id');

                                if (! $stateId) {
                                    return [];
                                }

                                return City::query()
                                    ->where('state_id', $stateId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => ! $get('state_id')),
                    ])
                    ->columns(3),

                Section::make('Legal Information')
                    ->schema([
                        TextInput::make('legal_name')
                            ->label('Legal Name')
                            ->maxLength(255)
                            ->dehydrateStateUsing(
                                fn ($state) => filled($state)
                                    ? mb_strtoupper($state)
                                    : $state
                            ),

                        TextInput::make('legal_address')
                            ->label('Legal Address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('contact_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Section::make('Airline Logo')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('airlines/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ]),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),
                    ]),
                // TextInput::make('name')
                //     ->required(),
                // TextInput::make('web'),
                // TextInput::make('phone')
                //     ->tel(),
                // TextInput::make('email')
                //     ->label('Email address')
                //     ->email(),
                // TextInput::make('ruc'),
                // TextInput::make('address'),
                // Select::make('country_id')
                //     ->relationship('country', 'name'),
                // Select::make('state_id')
                //     ->relationship('state', 'name'),
                // Select::make('city_id')
                //     ->relationship('city', 'name'),
                // TextInput::make('legal_name'),
                // TextInput::make('legal_address'),
                // TextInput::make('contact_email')
                //     ->email(),
                // TextInput::make('contact_phone')
                //     ->tel(),
                // TextInput::make('logo'),
                // Toggle::make('status')
                //     ->required(),
                // TextInput::make('created_by')
                //     ->numeric(),
                // TextInput::make('updated_by')
                //     ->numeric(),
            ]);
    }
}
