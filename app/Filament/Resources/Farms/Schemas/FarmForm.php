<?php

namespace App\Filament\Resources\Farms\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;

class FarmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                /*
                |--------------------------------------------------------------------------
                | Basic Information
                |--------------------------------------------------------------------------
                */

                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, $set) => $set(
                                    'name',
                                    filled($state) ? mb_strtoupper($state) : null
                                )
                            ),

                        TextInput::make('trade_name')
                            ->label('Trade Name')
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, $set) => $set(
                                    'trade_name',
                                    filled($state) ? mb_strtoupper($state) : null
                                )
                            ),

                        TextInput::make('ruc')
                            ->label('RUC')
                            ->maxLength(50),

                        TextInput::make('web')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),
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
                | Flower Varieties
                |--------------------------------------------------------------------------
                */

                Section::make('Flower Varieties')
                    ->schema([
                        Select::make('flowerVarieties')
                            ->label('Flower Varieties')
                            ->relationship('flowerVarieties', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Contact Information
                |--------------------------------------------------------------------------
                */

                Section::make('Contact Information')
                    ->schema([
                        Repeater::make('emails')
                            ->label('Emails')
                            ->schema([
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Email')
                            ->collapsible(),

                        Repeater::make('phones')
                            ->label('Phones')
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Phone')
                            ->collapsible(),
                    ])
                    ->columns(2),
                // TextInput::make('name')
                //     ->required(),
                // TextInput::make('trade_name'),
                // TextInput::make('ruc'),
                // TextInput::make('web'),
                // TextInput::make('address'),
                // TextInput::make('country_id')
                //     ->numeric(),
                // TextInput::make('state_id')
                //     ->numeric(),
                // TextInput::make('city_id')
                //     ->numeric(),
                // TextInput::make('emails')
                //     ->email(),
                // TextInput::make('phones')
                //     ->tel(),
                // Toggle::make('status')
                //     ->required(),
                // TextInput::make('created_by')
                //     ->numeric(),
                // TextInput::make('updated_by')
                //     ->numeric(),
            ]);
    }
}
