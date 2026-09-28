<?php

namespace App\Filament\Resources\FlightCoordinations\Pages;

use App\Filament\Resources\FlightCoordinations\FlightCoordinationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFlightCoordination extends CreateRecord
{
    protected static string $resource = FlightCoordinationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['flight_id'] = session()->get('flight_coordination_flight_id');

        return $data;
    }
}