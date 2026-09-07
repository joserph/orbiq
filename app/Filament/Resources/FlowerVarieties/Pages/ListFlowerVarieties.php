<?php

namespace App\Filament\Resources\FlowerVarieties\Pages;

use App\Filament\Resources\FlowerVarieties\FlowerVarietyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFlowerVarieties extends ListRecords
{
    protected static string $resource = FlowerVarietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->modalHeading('Create Flower Variety'),
        ];
    }
}
