<?php

namespace App\Filament\Resources\FlowerVarieties\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FlowerVarietyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->dehydrateStateUsing(
                        fn (?string $state): ?string => $state !== null
                            ? mb_strtoupper($state)
                            : null
                    ),
                TextInput::make('scientific_name')
                    ->required()
                    ->dehydrateStateUsing(
                        fn (?string $state): ?string => $state !== null
                            ? mb_strtoupper($state)
                            : null
                    ),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
