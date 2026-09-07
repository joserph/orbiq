<?php

namespace App\Filament\Resources\FlowerVarieties\Pages;

use App\Filament\Resources\FlowerVarieties\FlowerVarietyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFlowerVariety extends EditRecord
{
    protected static string $resource = FlowerVarietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
