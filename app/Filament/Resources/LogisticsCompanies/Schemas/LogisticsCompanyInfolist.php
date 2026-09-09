<?php

namespace App\Filament\Resources\LogisticsCompanies\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Infolist;
use Filament\Schemas\Schema;

class LogisticsCompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Company Name'),

                        TextEntry::make('company_type')
                            ->label('Company Type')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'AIRLINE' => 'Airline',
                                'SHIPPING_LINE' => 'Shipping Line',
                                'FREIGHT_FORWARDER' => 'Freight Forwarder',
                                'TRUCKING_COMPANY' => 'Trucking Company',
                                'CUSTOMS_BROKER' => 'Customs Broker',
                                default => $state,
                            }),
                        TextEntry::make('contact_email')
                            ->label('Email')
                            ->placeholder('-'),

                        TextEntry::make('contact_phone')
                            ->label('Phone')
                            ->placeholder('-'),

                        TextEntry::make('web')
                            ->label('Website')
                            ->url(fn ($state) => filled($state) ? $state : null)
                            ->openUrlInNewTab(),

                        TextEntry::make('ruc')
                            ->label('RUC'),
                    ])
                    ->columns(2),

                Section::make('Address')
                    ->schema([
                        TextEntry::make('address')
                            ->label('Address')
                            ->columnSpanFull(),
                    ]),

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

                Section::make('Company Logo')
                    ->schema([
                        ImageEntry::make('logo')
                            ->label('Logo')
                            ->disk('public')
                            ->height(120)
                            ->width(120),
                    ]),
                
                Section::make('Contact Information')
                    ->schema([
                        RepeatableEntry::make('emails')
                            ->label('Emails')
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email'),
                            ])
                            ->visible(fn ($state) => filled($state)),

                        RepeatableEntry::make('phones')
                            ->label('Phones')
                            ->schema([
                                TextEntry::make('phone')
                                    ->label('Phone'),
                            ])
                            ->visible(fn ($state) => filled($state)),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),
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