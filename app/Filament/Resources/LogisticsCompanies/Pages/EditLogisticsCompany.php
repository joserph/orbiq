<?php

namespace App\Filament\Resources\LogisticsCompanies\Pages;

use App\Filament\Resources\LogisticsCompanies\LogisticsCompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLogisticsCompany extends EditRecord
{
    protected static string $resource = LogisticsCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
