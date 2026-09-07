<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
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

                        TextEntry::make('zip_code')
                            ->label('ZIP Code')
                            ->placeholder('Not provided'),

                        TextEntry::make('address')
                            ->label('Address')
                            ->placeholder('Not provided')
                            ->columnSpanFull(),

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
                        TextEntry::make('country.name')
                            ->label('Country')
                            ->placeholder('Not provided'),

                        TextEntry::make('state.name')
                            ->label('State')
                            ->placeholder('Not provided'),

                        TextEntry::make('city.name')
                            ->label('City')
                            ->placeholder('Not provided'),
                    ])
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | Commercializers
                |--------------------------------------------------------------------------
                */

                Section::make('Commercializers')
                    ->schema([
                        RepeatableEntry::make('commercializers')
                            ->label('Commercializers')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name'),

                                TextEntry::make('trade_name')
                                    ->label('Trade Name')
                                    ->placeholder('Not provided'),
                            ])
                            ->columns(2),
                    ])
                    ->visible(
                        fn ($record) => $record->commercializers()->exists()
                    ),

                /*
                |--------------------------------------------------------------------------
                | Load & POA
                |--------------------------------------------------------------------------
                */

                Section::make('Load & POA')
                    ->schema([
                        TextEntry::make('load_types')
                            ->label('Load Types')
                            ->badge()
                            ->formatStateUsing(function ($state): string {
                                if (blank($state)) {
                                    return '';
                                }

                                if (is_string($state)) {
                                    $decodedState = json_decode($state, true);

                                    $state = is_array($decodedState)
                                        ? $decodedState
                                        : [$state];
                                }

                                if (! is_array($state)) {
                                    $state = [$state];
                                }

                                return implode(
                                    ', ',
                                    array_map(
                                        fn ($type) => match ($type) {
                                            'air' => 'Air',
                                            'maritime' => 'Maritime',
                                            default => ucfirst((string) $type),
                                        },
                                        $state
                                    )
                                );
                            }),

                        IconEntry::make('poa')
                            ->label('Has POA')
                            ->boolean(),

                        TextEntry::make('poa_document')
                            ->label('POA Document')
                            ->formatStateUsing(fn ($state) => filled($state) ? 'View Document' : null)
                            ->url(
                                fn ($record) => filled($record?->poa_document)
                                    ? \Illuminate\Support\Facades\Storage::disk('public')
                                        ->url($record->poa_document)
                                    : null
                            )
                            ->openUrlInNewTab()
                            ->visible(fn ($record) => filled($record?->poa_document)),
                    ])
                    ->columns(3),

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
                            ->visible(fn ($state) => filled($state)),

                        RepeatableEntry::make('owners')
                            ->label('Owners')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name'),

                                TextEntry::make('phone')
                                    ->label('Phone'),
                            ])
                            ->columns(2)
                            ->visible(fn ($state) => filled($state)),

                        RepeatableEntry::make('phones')
                            ->label('Phones')
                            ->schema([
                                TextEntry::make('phone')
                                    ->label('Phone'),
                            ])
                            ->visible(fn ($state) => filled($state)),
                    ])
                    ->columns(3)
                    ->visible(
                        fn ($record) => filled($record?->emails)
                            || filled($record?->owners)
                            || filled($record?->phones)
                    ),

                /*
                |--------------------------------------------------------------------------
                | Audit Information
                |--------------------------------------------------------------------------
                */

                Section::make('Audit Information')
                    ->schema([
                        TextEntry::make('createdBy.name')
                            ->label('Created By')
                            ->default('System'),

                        TextEntry::make('updatedBy.name')
                            ->label('Last Modified By')
                            ->default('System'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Last Modified At')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}