<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Models\Commercializer;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('zip_code')
                            ->label('ZIP Code')
                            ->maxLength(20),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),

                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('state_id', null);
                                $set('city_id', null);
                            }),

                        Select::make('state_id')
                            ->label('State')
                            ->options(function ($get) {
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
                            ->afterStateUpdated(function ($set) {
                                $set('city_id', null);
                            })
                            ->disabled(fn ($get) => blank($get('country_id'))),

                        Select::make('city_id')
                            ->label('City')
                            ->options(function ($get) {
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
                            ->disabled(fn ($get) => blank($get('state_id'))),
                    ])
                    ->columns(3),
                Section::make('Commercializers')
                    ->schema([
                        Select::make('commercializers')
                            ->label('Commercializers')
                            ->multiple()
                            ->relationship(
                                name: 'commercializers',
                                titleAttribute: 'name'
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Commercializer $record): string =>
                                    $record->trade_name
                                        ? "{$record->name} ({$record->trade_name})"
                                        : $record->name
                            )
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Load & POA')
                    ->schema([
                        CheckboxList::make('load_types')
                            ->label('Load Types')
                            ->options([
                                'air' => 'Air',
                                'maritime' => 'Maritime',
                            ])
                            ->columns(2)
                            ->required(),

                        Toggle::make('poa')
                            ->label('Has POA')
                            ->live(),

                        FileUpload::make('poa_document')
                            ->label('POA Document')
                            ->disk('public')
                            ->directory('clients/poa')
                            ->acceptedFileTypes([
                                'application/pdf',
                            ])
                            ->visible(fn ($get): bool => $get('poa') === true)
                            ->required(fn ($get): bool => $get('poa') === true)
                            ->downloadable()
                            ->openable(),
                    ])
                    ->columns(2),
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
                            ->addActionLabel('Add Email')
                            ->defaultItems(0)
                            ->collapsible(),

                        Repeater::make('owners')
                            ->label('Owners')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required(),

                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Owner')
                            ->defaultItems(0)
                            ->collapsible(),

                        Repeater::make('phones')
                            ->label('Phones')
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->required(),
                            ])
                            ->addActionLabel('Add Phone')
                            ->defaultItems(0)
                            ->collapsible(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                // TextInput::make('name')
                //     ->required(),
                // TextInput::make('zip_code'),
                // Textarea::make('address')
                //     ->columnSpanFull(),
                // Select::make('country_id')
                //     ->relationship('country', 'name'),
                // Select::make('state_id')
                //     ->relationship('state', 'name'),
                // Select::make('city_id')
                //     ->relationship('city', 'name'),
                // TextInput::make('load_types'),
                // Toggle::make('poa')
                //     ->required(),
                // TextInput::make('poa_document'),
                // TextInput::make('emails')
                //     ->email(),
                // TextInput::make('owners'),
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
