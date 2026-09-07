<?php

namespace App\Filament\Resources\FlowerVarieties\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Schema;

class FlowerVarietyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Name'),
                TextEntry::make('scientific_name')
                    ->label('Scientific Name'),
                IconEntry::make('status')
                    ->label('Status')
                    ->boolean(),
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
            ]);
    }
}