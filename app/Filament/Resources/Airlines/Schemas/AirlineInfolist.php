<?php

namespace App\Filament\Resources\Airlines\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Infolist;
use Filament\Schemas\Schema;

class AirlineInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Airline Name'),

                        TextEntry::make('web')
                            ->label('Website')
                            ->url(fn ($state) => filled($state) ? $state : null)
                            ->openUrlInNewTab(),

                        TextEntry::make('phone')
                            ->label('Phone'),

                        TextEntry::make('email')
                            ->label('Email'),

                        TextEntry::make('ruc')
                            ->label('RUC'),

                        TextEntry::make('address')
                            ->label('Address')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        TextEntry::make('country.name')
                            ->label('Country'),

                        TextEntry::make('state.name')
                            ->label('State'),

                        TextEntry::make('city.name')
                            ->label('City'),
                    ])
                    ->columns(3),

                Section::make('Legal Information')
                    ->schema([
                        TextEntry::make('legal_name')
                            ->label('Legal Name'),

                        TextEntry::make('legal_address')
                            ->label('Legal Address')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('contact_email')
                            ->label('Contact Email'),

                        TextEntry::make('contact_phone')
                            ->label('Contact Phone'),
                    ])
                    ->columns(2),

                Section::make('Airline Logo')
                    ->schema([
                        ImageEntry::make('logo')
                            ->label('Logo')
                            ->disk('public')
                            ->height(120)
                            ->width(120),
                    ]),

                Section::make('Status')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn ($state) => $state ? 'Active' : 'Inactive'
                            ),
                    ]),

                Section::make('Audit Information')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Created By'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),

                        TextEntry::make('updater.name')
                            ->label('Updated By'),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}