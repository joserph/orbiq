<?php

namespace App\Filament\Resources\Commercializers\Pages;

use App\Filament\Resources\Commercializers\CommercializerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommercializer extends EditRecord
{
    protected static string $resource = CommercializerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
