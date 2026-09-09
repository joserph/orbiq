<?php

namespace App\Filament\Resources\LogisticsCompanies\Pages;

use App\Filament\Resources\LogisticsCompanies\LogisticsCompanyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLogisticsCompanies extends ListRecords
{
    protected static string $resource = LogisticsCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->modalWidth('7xl'),
        ];
    }
}
