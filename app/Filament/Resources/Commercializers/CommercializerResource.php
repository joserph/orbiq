<?php

namespace App\Filament\Resources\Commercializers;

use App\Filament\Resources\Commercializers\Pages\CreateCommercializer;
use App\Filament\Resources\Commercializers\Pages\EditCommercializer;
use App\Filament\Resources\Commercializers\Pages\ListCommercializers;
use App\Filament\Resources\Commercializers\Schemas\CommercializerForm;
use App\Filament\Resources\Commercializers\Tables\CommercializersTable;
use App\Filament\Resources\Commercializers\Schemas\CommercializerInfolist;
use App\Models\Commercializer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommercializerResource extends Resource
{
    protected static ?string $model = Commercializer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CommercializerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CommercializerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommercializersTable::configure($table);
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
            'index' => ListCommercializers::route('/'),
            'create' => CreateCommercializer::route('/create'),
            'edit' => EditCommercializer::route('/{record}/edit'),
        ];
    }
}
