<?php

namespace App\Filament\Resources\LogisticsCompanies;

use App\Filament\Resources\LogisticsCompanies\Pages\CreateLogisticsCompany;
use App\Filament\Resources\LogisticsCompanies\Pages\EditLogisticsCompany;
use App\Filament\Resources\LogisticsCompanies\Pages\ListLogisticsCompanies;
use App\Filament\Resources\LogisticsCompanies\Schemas\LogisticsCompanyForm;
use App\Filament\Resources\LogisticsCompanies\Schemas\LogisticsCompanyInfolist;
use App\Filament\Resources\LogisticsCompanies\Tables\LogisticsCompaniesTable;
use App\Models\LogisticsCompany;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LogisticsCompanyResource extends Resource
{
    protected static ?string $model = LogisticsCompany::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Logistics Companies';

    protected static ?string $modelLabel = 'Logistics Company';

    protected static ?string $pluralModelLabel = 'Logistics Companies';

    public static function form(Schema $schema): Schema
    {
        return LogisticsCompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogisticsCompaniesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LogisticsCompanyInfolist::configure($schema);
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
            'index' => ListLogisticsCompanies::route('/'),
            'create' => CreateLogisticsCompany::route('/create'),
            'edit' => EditLogisticsCompany::route('/{record}/edit'),
        ];
    }
}
