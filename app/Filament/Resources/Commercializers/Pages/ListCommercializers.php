<?php

namespace App\Filament\Resources\Commercializers\Pages;

use App\Filament\Resources\Commercializers\CommercializerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommercializers extends ListRecords
{
    protected static string $resource = CommercializerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->modalWidth('7xl')
                ->modalHeading('Create Commercializer'),
        ];
    }
}
