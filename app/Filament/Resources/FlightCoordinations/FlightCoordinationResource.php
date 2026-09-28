<?php

namespace App\Filament\Resources\FlightCoordinations;

use App\Filament\Resources\FlightCoordinations\Pages\CreateFlightCoordination;
use App\Filament\Resources\FlightCoordinations\Pages\EditFlightCoordination;
use App\Filament\Resources\FlightCoordinations\Pages\ListFlightCoordinations;
use App\Filament\Resources\FlightCoordinations\Schemas\FlightCoordinationForm;
use App\Filament\Resources\FlightCoordinations\Tables\FlightCoordinationsTable;
use App\Models\FlightCoordination;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FlightCoordinationResource extends Resource
{
    protected static ?string $model = FlightCoordination::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return FlightCoordinationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlightCoordinationsTable::configure($table);
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
            // 'index' => ListFlightCoordinations::route('/'),
            // 'index' => ListFlightCoordinations::route('/{flight}/coordinations'),
            'index' => ListFlightCoordinations::route('/'),
            'create' => CreateFlightCoordination::route('/create'),
            'edit' => EditFlightCoordination::route('/{record}/edit'),
        ];
    }
}
