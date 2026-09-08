<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Company Information
                |--------------------------------------------------------------------------
                */

                Section::make('Company Information')
                    ->schema([
                        ImageEntry::make('logo')
                            ->label('Logo')
                            ->disk('public')
                            ->height(100),

                        TextEntry::make('name')
                            ->label('Company Name'),

                        TextEntry::make('web')
                            ->label('Website')
                            ->placeholder('-')
                            ->url(
                                fn ($record) => filled($record?->web)
                                    ? $record->web
                                    : null
                            )
                            ->openUrlInNewTab(),

                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('-'),

                        TextEntry::make('phone')
                            ->label('Phone')
                            ->placeholder('-'),

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

                        TextEntry::make('zip_code')
                            ->label('Zip Code')
                            ->placeholder('-'),

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
                | Legal Information
                |--------------------------------------------------------------------------
                */

                Section::make('Legal Information')
                    ->schema([
                        TextEntry::make('legal_name')
                            ->label('Legal Name')
                            ->placeholder('-'),

                        TextEntry::make('legal_address')
                            ->label('Legal Address')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Contact Information
                |--------------------------------------------------------------------------
                */

                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('contact_email')
                            ->label('Contact Email')
                            ->placeholder('-'),

                        TextEntry::make('contact_phone')
                            ->label('Contact Phone')
                            ->placeholder('-'),
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