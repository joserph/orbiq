<?php

namespace App\Filament\Resources\Farms\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FarmInfolist
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
                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('trade_name')
                            ->label('Trade Name')
                            ->placeholder('-'),

                        TextEntry::make('ruc')
                            ->label('RUC')
                            ->placeholder('-'),

                        TextEntry::make('web')
                            ->label('Website')
                            ->placeholder('-')
                            ->url(
                                fn ($record) => filled($record?->web)
                                    ? $record->web
                                    : null
                            )
                            ->openUrlInNewTab(),

                        IconEntry::make('status')
                            ->label('Status')
                            ->boolean(),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Location
                |--------------------------------------------------------------------------
                */

                Section::make('Location')
                    ->schema([
                        TextEntry::make('address')
                            ->label('Address')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('country.name')
                            ->label('Country')
                            ->placeholder('-'),

                        TextEntry::make('state.name')
                            ->label('State')
                            ->placeholder('-'),

                        TextEntry::make('city.name')
                            ->label('City')
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | Flower Varieties
                |--------------------------------------------------------------------------
                */

                Section::make('Flower Varieties')
                    ->schema([
                        TextEntry::make('flowerVarieties.name')
                            ->label('Varieties')
                            ->badge()
                            ->separator(','),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Contact Information
                |--------------------------------------------------------------------------
                */

                Section::make('Contact Information')
                    ->schema([

                        RepeatableEntry::make('emails')
                            ->label('Emails')
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email'),
                            ])
                            ->columns(1)
                            ->placeholder('No emails registered'),

                        RepeatableEntry::make('phones')
                            ->label('Phones')
                            ->schema([
                                TextEntry::make('phone')
                                    ->label('Phone'),
                            ])
                            ->columns(1)
                            ->placeholder('No phones registered'),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Audit Information
                |--------------------------------------------------------------------------
                */

                Section::make('Audit Information')
                    ->schema([
                        TextEntry::make('createdBy.name')
                            ->label('Created By')
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updatedBy.name')
                            ->label('Last Updated By')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}