<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                /*
                |--------------------------------------------------------------------------
                | General Information
                |--------------------------------------------------------------------------
                */

                Section::make('General Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, $set) => $set(
                                    'name',
                                    filled($state) ? mb_strtoupper($state) : null
                                )
                            ),

                        TextInput::make('web')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('zip_code')
                            ->label('Zip Code')
                            ->maxLength(20),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Legal Information
                |--------------------------------------------------------------------------
                */

                Section::make('Legal Information')
                    ->schema([
                        TextInput::make('legal_name')
                            ->label('Legal Name')
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, $set) => $set(
                                    'legal_name',
                                    filled($state) ? mb_strtoupper($state) : null
                                )
                            ),

                        TextInput::make('legal_address')
                            ->label('Legal Address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Location
                |--------------------------------------------------------------------------
                */

                Section::make('Location')
                    ->schema([
                        Select::make('country_id')
                            ->label('Country')
                            ->options(
                                Country::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn ($set) => [
                                $set('state_id', null),
                                $set('city_id', null),
                            ]),

                        Select::make('state_id')
                            ->label('State')
                            ->options(
                                fn ($get) => filled($get('country_id'))
                                    ? State::query()
                                        ->where('country_id', $get('country_id'))
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(
                                fn ($set) => $set('city_id', null)
                            ),

                        Select::make('city_id')
                            ->label('City')
                            ->options(
                                fn ($get) => filled($get('state_id'))
                                    ? City::query()
                                        ->where('state_id', $get('state_id'))
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                    : []
                            )
                            ->searchable(),
                    ])
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | Contact Information
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Company Logo
                |--------------------------------------------------------------------------
                */

                Section::make('Company Logo')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('companies/logos')
                            ->disk('public')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ]),
                    ]),
                // TextInput::make('name')
                //     ->required(),
                // TextInput::make('legal_name'),
                // TextInput::make('web'),
                // TextInput::make('phone')
                //     ->tel(),
                // TextInput::make('email')
                //     ->label('Email address')
                //     ->email(),
                // TextInput::make('address'),
                // TextInput::make('zip_code'),
                // Select::make('country_id')
                //     ->relationship('country', 'name'),
                // Select::make('state_id')
                //     ->relationship('state', 'name'),
                // Select::make('city_id')
                //     ->relationship('city', 'name'),
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
