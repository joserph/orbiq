<?php

namespace App\Filament\Resources\Loads;

use App\Filament\Resources\Loads\Pages\CreateLoad;
use App\Filament\Resources\Loads\Pages\EditLoad;
use App\Filament\Resources\Loads\Pages\ListLoads;
use App\Filament\Resources\Loads\Schemas\LoadForm;
use App\Filament\Resources\Loads\Tables\LoadsTable;
use App\Models\Load;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoadResource extends Resource
{
    protected static ?string $model = Load::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'bl';

    public static function form(Schema $schema): Schema
    {
        return LoadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoads::route('/'),
            'create' => CreateLoad::route('/create'),
            'edit' => EditLoad::route('/{record}/edit'),
        ];
    }
}
