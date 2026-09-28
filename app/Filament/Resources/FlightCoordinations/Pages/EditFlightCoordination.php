<?php

namespace App\Filament\Resources\FlightCoordinations\Pages;

use App\Filament\Resources\FlightCoordinations\FlightCoordinationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFlightCoordination extends EditRecord
{
    protected static string $resource = FlightCoordinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
