<?php

namespace App\Filament\Resources\Commercializers\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercializerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Basic Information')
                    ->schema([

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'individual' => 'Individual',
                                'company' => 'Company',
                            ])
                            ->required()
                            ->default('company'),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(
                                fn (?string $state): ?string => filled($state)
                                    ? mb_strtoupper($state)
                                    : $state
                            ),

                        TextInput::make('trade_name')
                            ->label('Trade Name')
                            ->maxLength(255)
                            ->dehydrateStateUsing(
                                fn (?string $state): ?string => filled($state)
                                    ? mb_strtoupper($state)
                                    : $state
                            ),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),

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
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->addActionLabel('Add Email')
                            ->defaultItems(0)
                            ->collapsible(),

                        Repeater::make('phones')
                            ->label('Phones')
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(50),
                            ])
                            ->addActionLabel('Add Phone')
                            ->defaultItems(0)
                            ->collapsible(),

                    ])
                    ->columns(2),

                Section::make('Staff')
                    ->schema([

                        Repeater::make('staffs')
                            ->label('Staff Members')
                            ->schema([

                                TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('position')
                                    ->label('Position')
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->maxLength(50),

                            ])
                            ->columns(2)
                            ->addActionLabel('Add Staff Member')
                            ->defaultItems(0)
                            ->collapsible(),

                    ])->columnSpan(2),
            ]);
    }
}