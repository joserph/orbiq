<?php

namespace App\Filament\Resources\Commercializers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercializerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('type')
                            ->label('Type')
                            ->formatStateUsing(
                                fn (string $state): string => match ($state) {
                                    'individual' => 'Individual',
                                    'company' => 'Company',
                                    default => $state,
                                }
                            ),

                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('trade_name')
                            ->label('Trade Name')
                            ->placeholder('Not provided'),

                        IconEntry::make('status')
                            ->label('Status')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([

                        RepeatableEntry::make('emails')
                            ->label('Emails')
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email'),
                            ])
                            ->columns(1)
                            ->visible(fn ($state) => filled($state)),

                        RepeatableEntry::make('phones')
                            ->label('Phones')
                            ->schema([
                                TextEntry::make('phone')
                                    ->label('Phone'),
                            ])
                            ->columns(1)
                            ->visible(fn ($state) => filled($state)),

                    ])
                    ->columns(2)
                    ->visible(
                        fn ($record) => filled($record?->emails)
                            || filled($record?->phones)
                    ),

                Section::make('Staff')
                    ->schema([

                        RepeatableEntry::make('staffs')
                            ->label('Staff Members')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name'),

                                TextEntry::make('position')
                                    ->label('Position'),

                                TextEntry::make('email')
                                    ->label('Email'),

                                TextEntry::make('phone')
                                    ->label('Phone'),
                            ])
                            ->columns(2),

                    ])->visible(fn ($record) => filled($record?->staffs)),

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