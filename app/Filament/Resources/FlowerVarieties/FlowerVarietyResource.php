<?php

namespace App\Filament\Resources\FlowerVarieties;

use App\Filament\Resources\FlowerVarieties\Pages\CreateFlowerVariety;
use App\Filament\Resources\FlowerVarieties\Pages\EditFlowerVariety;
use App\Filament\Resources\FlowerVarieties\Pages\ListFlowerVarieties;
use App\Filament\Resources\FlowerVarieties\Schemas\FlowerVarietyForm;
use App\Filament\Resources\FlowerVarieties\Tables\FlowerVarietiesTable;
use App\Filament\Resources\FlowerVarieties\Schemas\FlowerVarietyInfolist;
use App\Models\FlowerVariety;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FlowerVarietyResource extends Resource
{
    protected static ?string $model = FlowerVariety::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FlowerVarietyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FlowerVarietyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlowerVarietiesTable::configure($table);
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
            'index' => ListFlowerVarieties::route('/'),
            'create' => CreateFlowerVariety::route('/create'),
            'edit' => EditFlowerVariety::route('/{record}/edit'),
        ];
    }
}
